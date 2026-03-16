<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

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
    createForm.value = { name: '', email: '', password: '' };
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
  <div class="min-h-screen bg-gray-100 py-10 px-4">
    <div class="max-w-5xl mx-auto space-y-6">
      <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Usuarios</h1>
          <p class="text-gray-600 mt-1">CRUD de usuarios do sistema.</p>
        </div>
      </div>

      <div v-if="errorMessages.length" class="p-4 rounded-lg bg-red-100 border border-red-300 text-red-700">
        <ul class="list-disc pl-5 space-y-1">
          <li v-for="(message, idx) in errorMessages" :key="`err-${idx}`" class="text-sm">
            {{ message }}
          </li>
        </ul>
      </div>
      <div v-if="success" class="p-4 rounded-lg bg-green-100 border border-green-300 text-green-700">
        {{ success }}
      </div>

      <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Cadastrar usuario</h2>
        <form class="grid md:grid-cols-3 gap-4" @submit.prevent="handleCreate">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
            <input
              v-model="createForm.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              v-model="createForm.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
            <input
              v-model="createForm.password"
              type="password"
              required
              minlength="8"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div class="md:col-span-3 flex justify-end">
            <button
              type="submit"
              :disabled="createLoading"
              class="px-5 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 disabled:bg-gray-400 transition"
            >
              {{ createLoading ? 'Salvando...' : 'Cadastrar' }}
            </button>
          </div>
        </form>
      </div>

      <div class="bg-white rounded-xl shadow-md">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-xl font-semibold text-gray-800">Lista de usuarios</h2>
        </div>

        <div v-if="loading" class="p-6 text-gray-500">Carregando usuarios...</div>
        <div v-else-if="users.length === 0" class="p-6 text-gray-500">Nenhum usuario cadastrado.</div>

        <div v-else class="divide-y divide-gray-200">
          <div
            v-for="user in users"
            :key="user.id"
            class="p-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
          >
            <div>
              <p class="text-lg font-semibold text-gray-900">{{ user.name }}</p>
              <p class="text-sm text-gray-500">{{ user.email }}</p>
            </div>
            <div class="flex gap-2">
              <button
                type="button"
                class="px-3 py-2 text-sm font-medium rounded-lg border border-blue-300 text-blue-700 hover:bg-blue-50"
                @click="startEdit(user)"
              >
                Editar
              </button>
              <button
                type="button"
                class="px-3 py-2 text-sm font-medium rounded-lg border border-red-300 text-red-700 hover:bg-red-50 disabled:opacity-50"
                :disabled="user.id === $page.props.auth.user?.id"
                @click="handleDelete(user.id)"
              >
                Remover
              </button>
            </div>

            <div v-if="editingId === user.id" class="w-full bg-gray-50 rounded-lg p-4">
              <h4 class="text-sm font-semibold text-gray-700 mb-3">Editar usuario</h4>
              <form class="grid md:grid-cols-3 gap-4" @submit.prevent="handleUpdate(user.id)">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                  <input
                    v-model="editForm.name"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                  <input
                    v-model="editForm.email"
                    type="email"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Nova senha (opcional)</label>
                  <input
                    v-model="editForm.password"
                    type="password"
                    minlength="8"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div class="md:col-span-3 flex justify-end gap-2">
                  <button
                    type="button"
                    class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
                    @click="cancelEdit"
                  >
                    Cancelar
                  </button>
                  <button
                    type="submit"
                    :disabled="editLoading"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:bg-gray-400"
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
