<template>
  <div class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
      <!-- Carregando -->
      <div v-if="loading" class="text-center">
        <p class="text-gray-600">Verificando sistema...</p>
      </div>

      <template v-else>
        <!-- Status do sistema -->
        <div v-if="status" class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-800 rounded">
          {{ status }}
        </div>

        <!-- Formulário de Registro (apenas se não houver usuários) -->
        <div v-if="canRegister">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">
          Criar Conta
        </h1>

        <!-- Mensagens de Erro -->
        <div v-if="errors" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
          <ul class="list-disc pl-5">
            <li v-for="(error, key) in errors" :key="key" class="text-sm">
              {{ Array.isArray(error) ? error[0] : error }}
            </li>
          </ul>
        </div>

        <!-- Formulário -->
        <form @submit.prevent="handleRegister">
          <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
              Nome
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Seu nome completo"
            />
          </div>

          <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
              Email
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="seu@email.com"
            />
          </div>

          <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
              Senha
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              minlength="8"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Mínimo 8 caracteres"
            />
          </div>

          <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
              Confirmar Senha
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              minlength="8"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Confirme sua senha"
            />
          </div>

          <button
            type="submit"
            :disabled="registerLoading"
            class="w-full bg-green-500 text-white py-2 px-4 rounded-lg font-medium hover:bg-green-600 disabled:bg-gray-400 transition"
          >
            {{ registerLoading ? 'Criando conta...' : 'Criar Conta' }}
          </button>
        </form>

        <p class="text-center text-gray-600 text-sm mt-4">
          Este é o primeiro acesso. Após criar sua conta, o sistema será restrito a este único usuário.
        </p>
      </div>

        <!-- Formulário de Login (quando já existe usuário) -->
        <div v-else>
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">
          Sistema de Produtos
        </h1>

        <!-- Mensagens de Erro -->
        <div v-if="errors" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
          <ul class="list-disc pl-5">
            <li v-for="(error, key) in errors" :key="key" class="text-sm">
              {{ Array.isArray(error) ? error[0] : error }}
            </li>
          </ul>
        </div>

        <!-- Mensagem de Sucesso -->
        <div v-if="message" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
          {{ message }}
        </div>

        <!-- Formulário de Login -->
        <form @submit.prevent="handleLogin">
          <div class="mb-4">
            <label for="login-email" class="block text-sm font-medium text-gray-700 mb-1">
              Email
            </label>
            <input
              id="login-email"
              v-model="loginForm.email"
              type="email"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="seu@email.com"
            />
          </div>

          <div class="mb-6">
            <label for="login-password" class="block text-sm font-medium text-gray-700 mb-1">
              Senha
            </label>
            <input
              id="login-password"
              v-model="loginForm.password"
              type="password"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="sua senha"
            />
          </div>

          <button
            type="submit"
            :disabled="loginLoading"
            class="w-full bg-blue-500 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-600 disabled:bg-gray-400 transition"
          >
            {{ loginLoading ? 'Entrando...' : 'Entrar' }}
          </button>
        </form>

        <p class="text-center text-gray-600 text-sm mt-4">
          Apenas um usuário pode acessar este sistema.
        </p>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const status = computed(() => page.props?.status || null);
const loading = ref(true);
const canRegister = ref(false);
const registerLoading = ref(false);
const loginLoading = ref(false);
const errors = ref(null);
const message = ref(null);

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const loginForm = ref({
  email: '',
  password: '',
});

// Verificar se pode registrar
const checkCanRegister = async () => {
  try {
    const response = await fetch('/api/can-register');
    const data = await response.json();
    canRegister.value = data.can_register;
  } catch (error) {
    console.error('Erro ao verificar registro:', error);
  } finally {
    loading.value = false;
  }
};

// Registrar novo usuário
const handleRegister = async () => {
  registerLoading.value = true;
  errors.value = null;

  try {
    const response = await fetch('/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(form.value),
    });

    const data = await response.json();

    if (!response.ok) {
      errors.value = data.errors || { register: ['Erro ao registrar'] };
      return;
    }

    // Armazenar token
    localStorage.setItem('auth_token', data.token);
    localStorage.setItem('user', JSON.stringify(data.user));

    message.value = 'Conta criada com sucesso! Entrando...';
    
    // Redirecionar para a página de produtos após 1 segundo
    setTimeout(() => {
      router.visit('/products');
    }, 1000);
  } catch (error) {
    console.error('Erro:', error);
    errors.value = { error: ['Erro ao conectar ao servidor'] };
  } finally {
    registerLoading.value = false;
  }
};

// Login
const handleLogin = async () => {
  loginLoading.value = true;
  errors.value = null;

  try {
    const response = await fetch('/api/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(loginForm.value),
    });

    const data = await response.json();

    if (!response.ok) {
      errors.value = data.errors || { email: ['Erro ao fazer login'] };
      return;
    }

    // Armazenar token
    localStorage.setItem('auth_token', data.token);
    localStorage.setItem('user', JSON.stringify(data.user));

    message.value = 'Login realizado com sucesso!';
    
    // Redirecionar para a página de produtos após 1 segundo
    setTimeout(() => {
      router.visit('/products');
    }, 1000);
  } catch (error) {
    console.error('Erro:', error);
    errors.value = { error: ['Erro ao conectar ao servidor'] };
  } finally {
    loginLoading.value = false;
  }
};

onMounted(() => {
  checkCanRegister();
});
</script>
