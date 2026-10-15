import { ref } from 'vue';
import userValidation from './user.validation';
import { useRejectedPasswords } from '@/common/validation/user';
import { useVuelidate } from '@vuelidate/core';
import { requiredIf } from '@vuelidate/validators';
import { addEditedWatcher } from '@/common/lib';

export function useForm (state, userId = null) {
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

    const userValidations = userValidation(userId);
    const { allowed, reject: rejectPassword } = useRejectedPasswords();
    const v$ = useVuelidate({
        user: {
            ...userValidations,
            password: {
                ...userValidations.password,
                required: requiredIf(() => user.value.setPassword),
                allowed,
            },
        },
    }, { user });

    const edited = ref(false);
    addEditedWatcher(state, edited, user);

    return {
        user,
        edited,
        v$,
        rejectPassword,
    };
}
