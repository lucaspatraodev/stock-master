<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    passwordStatus: {
        type: String,
    },
});
</script>

<template>
    <Head title="Perfil" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-900"
            >
                Perfil
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div v-if="status || passwordStatus" class="space-y-3">
                    <div
                        v-if="status"
                        class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"
                    >
                        {{ status }}
                    </div>
                    <div
                        v-if="passwordStatus"
                        class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700"
                    >
                        {{ passwordStatus }}
                    </div>
                </div>

                <div
                    class="bg-white p-6 shadow-sm ring-1 ring-gray-100 sm:rounded-xl"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-2xl"
                    />
                </div>

                <div
                    class="bg-white p-6 shadow-sm ring-1 ring-gray-100 sm:rounded-xl"
                >
                    <UpdatePasswordForm class="max-w-2xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
