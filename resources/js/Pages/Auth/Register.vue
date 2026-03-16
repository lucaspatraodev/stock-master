<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const errorList = computed(() =>
    Object.values(form.errors || {}).flat()
);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <div class="min-h-screen bg-[#131314] flex items-center justify-center px-4">
        <Head title="Criar conta" />

        <div
            class="bg-zinc-950 p-8 rounded-2xl shadow-[0_25px_60px_-40px_rgba(0,0,0,0.8)] w-full max-w-md border border-zinc-900"
        >
            <div class="mb-6 text-center">
                <p class="text-lg font-bold tracking-[0.2em] text-white">
                    STOCKMASTER
                </p>
                <h1 class="text-2xl font-semibold text-white mt-2">
                    Criar Conta
                </h1>
            </div>

            <div
                v-if="errorList.length"
                class="mb-4 p-4 bg-red-500/10 border border-red-500/40 text-red-200 rounded-lg"
            >
                <ul class="list-disc pl-5">
                    <li v-for="(error, index) in errorList" :key="index" class="text-sm">
                        {{ error }}
                    </li>
                </ul>
            </div>

            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-1">
                        Nome
                    </label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        class="w-full px-4 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                        placeholder="Seu nome completo"
                    />
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">
                        Email
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="username"
                        class="w-full px-4 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                        placeholder="seu@email.com"
                    />
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">
                        Senha
                    </label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        class="w-full px-4 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                        placeholder="Mínimo 8 caracteres"
                    />
                </div>

                <div class="mb-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-1">
                        Confirmar Senha
                    </label>
                    <input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        class="w-full px-4 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                        placeholder="Confirme sua senha"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-lime-500 text-black py-2 px-4 rounded-lg font-semibold hover:bg-lime-400 disabled:bg-zinc-700 transition"
                >
                    {{ form.processing ? 'Criando conta...' : 'Criar Conta' }}
                </button>
            </form>

            <div class="mt-4 text-center text-sm text-gray-400">
                <span>Já tem conta?</span>
                <Link
                    :href="route('login')"
                    class="ml-1 text-lime-400 hover:text-lime-300 font-medium"
                >
                    Entrar
                </Link>
            </div>
        </div>
    </div>
</template>
