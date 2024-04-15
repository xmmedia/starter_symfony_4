import { computed, ref, watch } from 'vue';
import FieldEmail from '@/common/field_email';
import FieldPassword from './component/field_password.vue';
import FieldInput from '@/common/field_input';
import FieldRole from './component/field_role.vue';
import userValidation from '@/admin/user/user.validation';
import { useVuelidate } from '@vuelidate/core';
import { requiredIf } from '@vuelidate/validators';
import debounce from 'lodash/debounce';
import { addEditedWatcher, editedWatcher } from '@/common/lib';

export function useForm (state) {
    const user = ref({
        email: null,
        setPassword: false,
        password: null,
        role: 'ROLE_USER',
        active: true,
        firstName: null,
        lastName: null,
        sendInvite: false,
        phoneNumber: null,
    });

    const userDataForPassword = computed(() => [
        user.value.email,
        user.value.firstName,
        user.value.lastName,
    ]);

    const userValidations = userValidation();
    const v$ = useVuelidate({
        user: {
            ...userValidations,
            password: {
                ...userValidations.password,
                required: requiredIf(user.value.setPassword),
            },
        },
    }, { user });

    const edited = ref(false);
    addEditedWatcher(state, edited, user);

    const setEmailDebounce = debounce(function (email) {
        setEmail(email);
    }, 100, { leading: true });
    function setEmail (value) {
        user.value.email = value;
    }

    return {
        user,
        userDataForPassword,

        FieldEmail,
        FieldPassword,
        FieldInput,
        FieldRole,

        edited,
        v$,

        setEmailDebounce,
    };
}
