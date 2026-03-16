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
                class="text-xl font-semibold leading-tight text-white"
            >
                Perfil
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
                <div v-if="status || passwordStatus" class="space-y-3">
                    <div
                        v-if="status"
                        class="rounded-lg border border-lime-500/40 bg-lime-500/10 px-4 py-3 text-sm text-lime-200"
                    >
                        {{ status }}
                    </div>
                    <div
                        v-if="passwordStatus"
                        class="rounded-lg border border-lime-500/40 bg-lime-500/10 px-4 py-3 text-sm text-lime-200"
                    >
                        {{ passwordStatus }}
                    </div>
                </div>

                <div
                    class="bg-zinc-950 p-6 border border-zinc-900 shadow-[0_20px_50px_-35px_rgba(0,0,0,0.8)] sm:rounded-2xl"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-2xl"
                    />
                </div>

                <div
                    class="bg-zinc-950 p-6 border border-zinc-900 shadow-[0_20px_50px_-35px_rgba(0,0,0,0.8)] sm:rounded-2xl"
                >
                    <UpdatePasswordForm class="max-w-2xl" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
