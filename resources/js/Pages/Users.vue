<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const loading = ref(true);
const users = ref([]);
const errorMessages = ref([]);
const success = ref(null);

const createLoading = ref(false);
const editLoading = ref(false);

const createForm = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const editingId = ref(null);
const editForm = ref({
  name: '',
  email: '',
  password: '',
});

const getToken = () => localStorage.getItem('auth_token');

const authHeaders = () => ({
  Authorization: `Bearer ${getToken()}`,
  Accept: 'application/json',
});

const normalizeCollection = (payload) => (Array.isArray(payload?.data) ? payload.data : payload || []);
const normalizeItem = (payload) => (payload?.data ? payload.data : payload);

const resetMessages = () => {
  errorMessages.value = [];
  success.value = null;
};

const setErrorsFromResponse = (data, fallback) => {
  if (data?.errors) {
    const flattened = Object.values(data.errors).flat().filter(Boolean);
    errorMessages.value = flattened.length > 0 ? flattened : [fallback];
    return;
  }
  if (data?.message) {
    errorMessages.value = [data.message];
    return;
  }
  errorMessages.value = [fallback];
};

const fetchUsers = async () => {
  resetMessages();
  try {
    const response = await fetch('/api/users', {
      headers: authHeaders(),
    });

    if (response.status === 401) {
      router.visit('/login');
      return;
    }

    const data = await response.json();
    if (!response.ok) {
      setErrorsFromResponse(data, 'Erro ao carregar usuarios.');
      return;
    }

    users.value = normalizeCollection(data);
  } catch (fetchError) {
    console.error(fetchError);
    errorMessages.value = ['Erro ao conectar ao servidor.'];
  }
};

const handleCreate = async () => {
  resetMessages();
  if (createForm.value.password !== createForm.value.password_confirmation) {
    errorMessages.value = ['Senha e repetir senha precisam ser iguais.'];
    return;
  }
  createLoading.value = true;

  try {
    const response = await fetch('/api/users', {
      method: 'POST',
      headers: {
        ...authHeaders(),
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(createForm.value),
    });

    const data = await response.json();
    if (!response.ok) {
      setErrorsFromResponse(data, 'Erro ao criar usuario.');
      return;
    }

    const createdUser = normalizeItem(data);
    users.value = [...users.value, createdUser].sort((a, b) => a.name.localeCompare(b.name));
    createForm.value = { name: '', email: '', password: '', password_confirmation: '' };
    success.value = 'Usuario criado com sucesso!';
  } catch (fetchError) {
    console.error(fetchError);
    errorMessages.value = ['Erro ao conectar ao servidor.'];
  } finally {
    createLoading.value = false;
  }
};

const startEdit = (user) => {
  resetMessages();
  editingId.value = editingId.value === user.id ? null : user.id;
  if (editingId.value === null) {
    return;
  }
  editForm.value = {
    name: user.name,
    email: user.email,
    password: '',
  };
};

const cancelEdit = () => {
  editingId.value = null;
  editForm.value = { name: '', email: '', password: '' };
};

const handleUpdate = async (userId) => {
  resetMessages();
  editLoading.value = true;

  try {
    const response = await fetch(`/api/users/${userId}`, {
      method: 'PUT',
      headers: {
        ...authHeaders(),
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(editForm.value),
    });

    const data = await response.json();
    if (!response.ok) {
      setErrorsFromResponse(data, 'Erro ao atualizar usuario.');
      return;
    }

    const updatedUser = normalizeItem(data);
    users.value = users.value.map((item) => (item.id === userId ? updatedUser : item));
    success.value = 'Usuario atualizado com sucesso!';
    cancelEdit();
  } catch (fetchError) {
    console.error(fetchError);
    errorMessages.value = ['Erro ao conectar ao servidor.'];
  } finally {
    editLoading.value = false;
  }
};

const handleDelete = async (userId) => {
  resetMessages();

  try {
    const response = await fetch(`/api/users/${userId}`, {
      method: 'DELETE',
      headers: authHeaders(),
    });

    const data = await response.json();
    if (!response.ok) {
      setErrorsFromResponse(data, 'Erro ao remover usuario.');
      return;
    }

    users.value = users.value.filter((item) => item.id !== userId);
    success.value = data?.message || 'Usuario removido.';
  } catch (fetchError) {
    console.error(fetchError);
    errorMessages.value = ['Erro ao conectar ao servidor.'];
  }
};

onMounted(async () => {
  const token = getToken();
  if (!token) {
    router.visit('/login');
    return;
  }

  await fetchUsers();
  loading.value = false;
});
</script>

<template>
  <div class="min-h-screen bg-[#131314] py-10 px-4">
    <div class="max-w-6xl mx-auto space-y-6">
      <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
          <p class="text-lg font-bold tracking-[0.2em] text-white">STOCKMASTER</p>
          <h1 class="text-3xl font-semibold text-white mt-2">Usuarios</h1>
          <p class="text-gray-400 mt-1">CRUD de usuarios do sistema.</p>
        </div>
        <Link
          :href="route('products')"
          class="px-4 py-2 border border-zinc-700 text-gray-200 rounded-lg font-medium hover:bg-zinc-900 transition"
        >
          Voltar
        </Link>
      </div>

      <div v-if="errorMessages.length" class="p-4 rounded-lg bg-red-500/10 border border-red-500/40 text-red-200">
        <ul class="list-disc pl-5 space-y-1">
          <li v-for="(message, idx) in errorMessages" :key="`err-${idx}`" class="text-sm">
            {{ message }}
          </li>
        </ul>
      </div>
      <div v-if="success" class="p-4 rounded-lg bg-lime-500/10 border border-lime-500/40 text-lime-200">
        {{ success }}
      </div>

      <div class="bg-zinc-950 rounded-2xl border border-zinc-900 shadow-[0_20px_50px_-35px_rgba(0,0,0,0.8)] p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Cadastrar usuario</h2>
        <form class="grid md:grid-cols-2 gap-4" @submit.prevent="handleCreate">
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Nome</label>
            <input
              v-model="createForm.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
            <input
              v-model="createForm.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Senha</label>
            <input
              v-model="createForm.password"
              type="password"
              required
              minlength="8"
              class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Repetir senha</label>
            <input
              v-model="createForm.password_confirmation"
              type="password"
              required
              minlength="8"
              class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
            />
          </div>
          <div class="md:col-span-2 flex justify-end">
            <button
              type="submit"
              :disabled="createLoading"
              class="px-5 py-2 bg-lime-500 text-black rounded-lg font-semibold hover:bg-lime-400 disabled:bg-zinc-700 transition"
            >
              {{ createLoading ? 'Salvando...' : 'Cadastrar' }}
            </button>
          </div>
        </form>
      </div>

      <div class="bg-zinc-950 rounded-2xl border border-zinc-900 shadow-[0_20px_50px_-35px_rgba(0,0,0,0.8)]">
        <div class="p-6 border-b border-zinc-900">
          <h2 class="text-xl font-semibold text-white">Lista de usuarios</h2>
        </div>

        <div v-if="loading" class="p-6 text-gray-400">Carregando usuarios...</div>
        <div v-else-if="users.length === 0" class="p-6 text-gray-400">Nenhum usuario cadastrado.</div>

        <div v-else class="divide-y divide-zinc-900">
          <div
            v-for="user in users"
            :key="user.id"
            class="p-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
          >
            <div>
              <p class="text-lg font-semibold text-white">{{ user.name }}</p>
              <p class="text-sm text-gray-400">{{ user.email }}</p>
            </div>
            <div class="flex gap-2">
              <button
                type="button"
                class="px-3 py-2 text-sm font-medium rounded-lg border border-lime-500/40 text-lime-200 hover:bg-lime-500/10"
                @click="startEdit(user)"
              >
                Editar
              </button>
              <button
                type="button"
                class="px-3 py-2 text-sm font-medium rounded-lg border border-red-500/40 text-red-200 hover:bg-red-500/10 disabled:opacity-50"
                :disabled="user.id === $page.props.auth.user?.id"
                @click="handleDelete(user.id)"
              >
                Remover
              </button>
            </div>

            <div v-if="editingId === user.id" class="w-full bg-zinc-900/60 rounded-lg p-4 border border-zinc-800">
              <h4 class="text-sm font-semibold text-gray-200 mb-3">Editar usuario</h4>
              <form class="grid md:grid-cols-2 gap-4" @submit.prevent="handleUpdate(user.id)">
                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-1">Nome</label>
                  <input
                    v-model="editForm.name"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                  <input
                    v-model="editForm.email"
                    type="email"
                    required
                    class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-300 mb-1">Nova senha (opcional)</label>
                  <input
                    v-model="editForm.password"
                    type="password"
                    minlength="8"
                    class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                  />
                </div>
                <div class="md:col-span-2 flex justify-end gap-2">
                  <button
                    type="button"
                    class="px-4 py-2 text-sm font-medium rounded-lg border border-zinc-700 text-gray-200 hover:bg-zinc-900"
                    @click="cancelEdit"
                  >
                    Cancelar
                  </button>
                  <button
                    type="submit"
                    :disabled="editLoading"
                    class="px-4 py-2 text-sm font-semibold rounded-lg bg-lime-500 text-black hover:bg-lime-400 disabled:bg-zinc-700"
                  >
                    {{ editLoading ? 'Salvando...' : 'Salvar' }}
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
