<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
  canLogin: {
    type: Boolean,
  },
  canRegister: {
    type: Boolean,
  },
});
</script>

<template>
  <Head title="StockMaster" />

  <div class="min-h-screen bg-[#131314] text-gray-200">
    <div class="relative overflow-hidden">
      <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-40 right-0 h-96 w-96 rounded-full bg-lime-500/10 blur-[120px]"></div>
        <div class="absolute -bottom-32 left-0 h-96 w-96 rounded-full bg-emerald-500/10 blur-[140px]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(16,185,129,0.08),transparent_55%)]"></div>
      </div>

      <header class="relative z-10 px-6 pt-8">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">
          <div>
            <p class="text-lg font-bold tracking-[0.2em] text-white">STOCKMASTER</p>
            <p class="text-xs uppercase tracking-[0.3em] text-gray-500">Controle Inteligente</p>
          </div>

          <nav v-if="canLogin" class="flex items-center gap-3">
            <Link
              v-if="$page.props.auth?.user"
              :href="route('dashboard')"
              class="rounded-lg border border-zinc-700 px-4 py-2 text-sm font-medium text-gray-200 transition hover:bg-zinc-900"
            >
              Dashboard
            </Link>
            <template v-else>
              <Link
                :href="route('login')"
                class="rounded-lg border border-zinc-700 px-4 py-2 text-sm font-medium text-gray-200 transition hover:bg-zinc-900"
              >
                Entrar
              </Link>
              <Link
                v-if="canRegister"
                :href="route('register')"
                class="rounded-lg bg-lime-500 px-4 py-2 text-sm font-semibold text-black transition hover:bg-lime-400"
              >
                Criar conta
              </Link>
            </template>
          </nav>
        </div>
      </header>

      <section class="relative z-10 px-6 pb-16 pt-14">
        <div class="mx-auto grid max-w-6xl items-center gap-12 lg:grid-cols-2">
          <div>
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-lime-400">Stock Master</p>
            <h1 class="mt-4 text-4xl font-semibold text-white md:text-5xl">
              Estoque sob controle.
              <span class="block text-gray-300">Vendas no ritmo certo.</span>
            </h1>
            <p class="mt-5 text-lg text-gray-400">
              Centralize cadastro, custos e precificacao dos seus produtos com uma interface clara,
              segura e feita para equipes pequenas e eficientes.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
              <Link
                v-if="canLogin"
                :href="route('login')"
                class="rounded-lg bg-lime-500 px-6 py-3 text-sm font-semibold text-black transition hover:bg-lime-400"
              >
                Acessar sistema
              </Link>
              <a
                href="#recursos"
                class="rounded-lg border border-zinc-700 px-6 py-3 text-sm font-semibold text-gray-200 transition hover:bg-zinc-900"
              >
                Ver recursos
              </a>
            </div>
            <div class="mt-10 flex flex-wrap gap-6 text-sm text-gray-500">
              <div class="flex items-center gap-2">
                <span class="inline-flex h-2 w-2 rounded-full bg-lime-400"></span>
                Cadastro rapido
              </div>
              <div class="flex items-center gap-2">
                <span class="inline-flex h-2 w-2 rounded-full bg-lime-400"></span>
                Custos e margens
              </div>
              <div class="flex items-center gap-2">
                <span class="inline-flex h-2 w-2 rounded-full bg-lime-400"></span>
                Controle de usuarios
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>
