<template>
    <div class="signin-bg">
        <div class="relative z-50">
            <div class="form-wrap pb-8">
                <div class="form_header-wrap">
                    <h1 class="form-header"><slot name="heading"></slot></h1>
                </div>

                <div class="px-8">
                    <slot />
                </div>

            </div>

            <slot name="after"></slot>
        </div>
    </div>
</template>
<script setup lang="ts">
</script>
