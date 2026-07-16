#!/usr/bin/env bash
# Sets GitLab CI/CD variables for a site.
# Run once per scope (staging / production) per project.
#
# Prerequisites:
#   - glab CLI installed and authenticated (glab auth login)
#   - jq installed (brew install jq)
#
# Usage: ./setup_gitlab_ci_vars.sh <group/project>
# Example: ./setup_gitlab_ci_vars.sh xmmedia/my-client-site
#          ./setup_gitlab_ci_vars.sh xmmedia/company/my-client-site
set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

die() { echo -e "${RED}Error: $*${NC}" >&2; exit 1; }

command -v glab >/dev/null 2>&1 || die "glab CLI not installed. See: https://gitlab.com/gitlab-org/cli"
command -v jq   >/dev/null 2>&1 || die "jq not installed. Run: brew install jq"
glab auth status >/dev/null 2>&1 || die "Not authenticated with glab. Run: glab auth login"

if [[ $# -lt 1 ]]; then
    echo "Usage: $0 <group/project>"
    echo "Example: $0 xmmedia/my-client-site"
    echo "         $0 xmmedia/company/my-client-site"
    exit 1
fi

PROJECT="$1"

# ask <label> [default]
ask() {
    local label="$1" default="${2:-}" result
    if [[ -n "$default" ]]; then
        read -r -p "$label [$default]: " result
        echo "${result:-$default}"
    else
        read -r -p "$label: " result
        echo "$result"
    fi
}

# ask_required <label> [default] — re-prompts until non-empty
ask_required() {
    local label="$1" default="${2:-}" result
    while true; do
        result=$(ask "$label" "$default")
        [[ -n "$result" ]] && break
        echo -e "  ${RED}Required.${NC}" >&2
    done
    echo "$result"
}

VARIABLES_JSON=""
FAILED=0

fetch_variables() {
    local out
    if ! out=$(glab variable list -R "$PROJECT" --output json 2>&1); then
        die "Failed to fetch variables from GitLab: $out"
    fi
    if ! echo "$out" | jq -e 'if type == "array" then true else error end' >/dev/null 2>&1; then
        die "Unexpected response from GitLab: $out"
    fi
    VARIABLES_JSON="$out"
}

var_exists_all_scope() {
    echo "$VARIABLES_JSON" \
        | jq -e --arg n "$1" 'map(select(.key == $n and .environment_scope == "*")) | length > 0' \
        > /dev/null 2>&1
}

var_exists_scope() {
    echo "$VARIABLES_JSON" \
        | jq -e --arg n "$1" --arg s "$2" 'map(select(.key == $n and .environment_scope == $s)) | length > 0' \
        > /dev/null 2>&1
}

set_var() {
    local name="$1" value="$2" scope="$3"
    local args=(variable set "$name" --value "$value" --scope "$scope" --raw -R "$PROJECT")
    [[ "$SCOPE" == "production" ]] && args+=(--protected)
    local out
    if out=$(glab "${args[@]}" 2>&1); then
        printf "  ${GREEN}✓${NC} %-35s scope: %s\n" "$name" "$scope"
    else
        printf "  ${RED}✗${NC} %-35s scope: %s  — FAILED: %s\n" "$name" "$scope" "$out"
        FAILED=1
    fi
}

echo ""
echo -e "${BOLD}${CYAN}GitLab CI Variable Setup${NC}"
echo -e "Project: ${YELLOW}$PROJECT${NC}"
echo ""

# Scope -----------------------------------------------------------------------
echo "Which scope are you setting up?"
echo "  1) staging"
echo "  2) production"
read -r -p "Scope [1]: " scope_choice
case "${scope_choice:-1}" in
    2|production) SCOPE="production" ;;
    *) SCOPE="staging" ;;
esac

echo ""
echo -n "Checking existing GitLab variables..."
fetch_variables
SCOPED_VARS=("SSH_PRIVATE_KEY" "REMOTE_BASE" "REMOTE_SERVER" "REMOTE_USER" "REQUEST_CONTEXT_HOST" "VITE_SENTRY_DSN")
EXISTING=()
for var in "${SCOPED_VARS[@]}"; do
    if var_exists_scope "$var" "$SCOPE"; then
        EXISTING+=("$var")
    fi
done
if [[ "${#EXISTING[@]}" -gt 0 ]]; then
    echo ""
    die "The following variables already exist for scope '$SCOPE': ${EXISTING[*]}"
fi
echo -e " ${GREEN}none exist${NC}"

echo ""

# SSH key ---------------------------------------------------------------------
echo -e "${CYAN}SSH Private Key${NC}"
echo "  1) Generate a new key"
echo "  2) Use an existing key"
read -r -p "Choice [1]: " key_choice

if [[ "${key_choice:-1}" == "2" ]]; then
    KEY_FILE=$(ask "Path to SSH private key" "$HOME/.ssh/id_gitlab")
    [[ -f "$KEY_FILE" ]] || die "File not found: $KEY_FILE"
else
    KEY_FILE=$(ask "Save key to" "./id_gitlab")
    if [[ -f "$KEY_FILE" ]]; then
        read -r -p "Key already exists at $KEY_FILE — delete and regenerate? [y/N]: " del_confirm
        [[ "$(echo "$del_confirm" | tr '[:upper:]' '[:lower:]')" == "y" ]] || { echo "Aborted."; exit 0; }
        rm -f "$KEY_FILE" "${KEY_FILE}.pub"
    fi
    ssh-keygen -t ed25519 -C "ci@gitlab.com" -f "$KEY_FILE" -N "" || die "ssh-keygen failed"
    echo -e "  ${GREEN}✓${NC} Key generated: $KEY_FILE"
fi

SSH_KEY=$(< "$KEY_FILE")

echo ""

# Remote config ---------------------------------------------------------------
REMOTE_BASE=$(ask_required "REMOTE_BASE (e.g. /home/username/example.com)")
REMOTE_BASE="${REMOTE_BASE%/}"
[[ "$REMOTE_BASE" == */current ]] && die "REMOTE_BASE should not include /current"

REMOTE_SERVER=$(ask_required "REMOTE_SERVER (SSH hostname or IP)")
REMOTE_USER=$(ask_required  "REMOTE_USER (SSH username)")

echo ""

# REQUEST_CONTEXT_HOST --------------------------------------------------------
CONTEXT_HOST=$(ask_required "REQUEST_CONTEXT_HOST for $SCOPE (e.g. www.example.com)")
CONTEXT_HOST="${CONTEXT_HOST#https://}"
CONTEXT_HOST="${CONTEXT_HOST#http://}"
CONTEXT_HOST="${CONTEXT_HOST%/}"

# VITE_SENTRY_DSN ---------------------------------------------------------------
VITE_SENTRY_DSN=$(ask_required "VITE_SENTRY_DSN for $SCOPE (Sentry DSN URL)")

echo ""

# REMOTE_PORT and REQUEST_CONTEXT_SCHEME — only when missing at * scope -------
echo "Checking * scope variables..."
NEED_PORT="false"
NEED_SCHEME="false"
REMOTE_PORT="10022"
REQUEST_CONTEXT_SCHEME="https"

if var_exists_all_scope "REMOTE_PORT"; then
    echo "  REMOTE_PORT already exists at scope * — skipping"
else
    NEED_PORT="true"
    while true; do
        REMOTE_PORT=$(ask "REMOTE_PORT" "10022")
        [[ "$REMOTE_PORT" =~ ^[0-9]+$ ]] && break
        echo -e "  ${RED}Port must be a number.${NC}" >&2
    done
fi

if var_exists_all_scope "REQUEST_CONTEXT_SCHEME"; then
    echo "  REQUEST_CONTEXT_SCHEME already exists at scope * — skipping"
else
    NEED_SCHEME="true"
    REQUEST_CONTEXT_SCHEME=$(ask "REQUEST_CONTEXT_SCHEME" "https")
fi

# Summary ---------------------------------------------------------------------
echo ""
echo -e "${CYAN}Summary:${NC}"
printf "  %-35s scope: %s  (key file: %s)\n" "SSH_PRIVATE_KEY" "$SCOPE" "$KEY_FILE"
printf "  %-35s scope: %s  = %s\n" "REMOTE_BASE" "$SCOPE" "$REMOTE_BASE"
printf "  %-35s scope: %s  = %s\n" "REMOTE_SERVER" "$SCOPE" "$REMOTE_SERVER"
printf "  %-35s scope: %s  = %s\n" "REMOTE_USER" "$SCOPE" "$REMOTE_USER"
printf "  %-35s scope: %s  = %s\n" "REQUEST_CONTEXT_HOST" "$SCOPE" "$CONTEXT_HOST"
printf "  %-35s scope: %s  = %s\n" "VITE_SENTRY_DSN" "$SCOPE" "$VITE_SENTRY_DSN"
if [[ "$SCOPE" == "production" ]]; then
    printf "  %-35s scope: %s  = %s\n" "REQUEST_CONTEXT_HOST" "static" "$CONTEXT_HOST"
fi
if [[ "$NEED_PORT" == "true" ]]; then
    printf "  %-35s scope: %s  = %s\n" "REMOTE_PORT" "*" "$REMOTE_PORT"
fi
if [[ "$NEED_SCHEME" == "true" ]]; then
    printf "  %-35s scope: %s  = %s\n" "REQUEST_CONTEXT_SCHEME" "*" "$REQUEST_CONTEXT_SCHEME"
fi

echo ""
read -r -p "Proceed? [y/N]: " confirm
[[ "$(echo "$confirm" | tr '[:upper:]' '[:lower:]')" == "y" ]] || { echo "Aborted."; exit 0; }

# Set variables ---------------------------------------------------------------
echo ""
echo "Setting variables..."
set_var "SSH_PRIVATE_KEY"        "$SSH_KEY"        "$SCOPE"
set_var "REMOTE_BASE"            "$REMOTE_BASE"    "$SCOPE"
set_var "REMOTE_SERVER"          "$REMOTE_SERVER"  "$SCOPE"
set_var "REMOTE_USER"            "$REMOTE_USER"    "$SCOPE"
set_var "REQUEST_CONTEXT_HOST"   "$CONTEXT_HOST"   "$SCOPE"
set_var "VITE_SENTRY_DSN"        "$VITE_SENTRY_DSN" "$SCOPE"
if [[ "$SCOPE" == "production" ]]; then
    set_var "REQUEST_CONTEXT_HOST" "$CONTEXT_HOST" "static"
fi
if [[ "$NEED_PORT" == "true" ]]; then
    set_var "REMOTE_PORT" "$REMOTE_PORT" "*"
fi
if [[ "$NEED_SCHEME" == "true" ]]; then
    set_var "REQUEST_CONTEXT_SCHEME" "$REQUEST_CONTEXT_SCHEME" "*"
fi

echo ""
if [[ "$FAILED" -eq 1 ]]; then
    echo -e "${RED}Done with errors — one or more variables failed to set.${NC}"
    exit 1
fi
echo -e "${GREEN}Done!${NC}"

if [[ -f "${KEY_FILE}.pub" ]]; then
    echo ""
    echo -e "${BOLD}${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BOLD}  Add this public key to the server's ~/.ssh/authorized_keys${NC}"
    echo -e "${BOLD}${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    cat "${KEY_FILE}.pub"
    echo ""
    echo -e "${BOLD}${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    echo -e "${YELLOW}Once the public key is on the server, delete the local key files:${NC}"
    echo "  rm \"$KEY_FILE\" \"${KEY_FILE}.pub\""
fi
