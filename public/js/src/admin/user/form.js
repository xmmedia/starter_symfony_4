import { ref } from 'vue';
import userValidation from '@/admin/user/user.validation';
import { useVuelidate } from '@vuelidate/core';
import { requiredIf } from '@vuelidate/validators';
import { addEditedWatcher } from '@/common/lib';

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

    const userValidations = userValidation();
    const v$ = useVuelidate({
        user: {
            ...userValidations,
            password: {
                ...userValidations.password,
                required: requiredIf(() => user.value.setPassword),
            },
        },
    }, { user });

    const edited = ref(false);
    addEditedWatcher(state, edited, user);

    return {
        user,
        edited,
        v$,
    };
}
