<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const loading = ref(true);
const products = ref([]);
const error = ref(null);
const success = ref(null);

const showCreate = ref(true);
const createLoading = ref(false);
const editLoading = ref(false);

const createForm = ref({
  title: '',
  description: '',
  sale_price: 0,
  cost: 0,
});

const createImages = ref([]);

const editingId = ref(null);
const editForm = ref({
  title: '',
  description: '',
  sale_price: 0,
  cost: 0,
});

const editImages = ref([]);

const getToken = () => localStorage.getItem('auth_token');

const authHeaders = () => ({
  Authorization: `Bearer ${getToken()}`,
});

const formatPrice = (value) => {
  if (value === null || value === undefined) return 'R$ 0,00';
  const numberValue = Number(value);
  if (Number.isNaN(numberValue)) return 'R$ 0,00';
  return numberValue.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const fetchProducts = async () => {
  error.value = null;
  try {
    const response = await fetch('/api/products', {
      headers: authHeaders(),
    });

    if (response.status === 401) {
      router.visit('/login');
      return;
    }

    const data = await response.json();
    if (!response.ok) {
      error.value = data?.message || 'Erro ao carregar produtos.';
      return;
    }

    products.value = data;
  } catch (fetchError) {
    console.error(fetchError);
    error.value = 'Erro ao conectar ao servidor.';
  }
};

const resetMessages = () => {
  error.value = null;
  success.value = null;
};

const handleCreate = async () => {
  resetMessages();
  createLoading.value = true;

  try {
    const payload = new FormData();
    Object.entries(createForm.value).forEach(([key, value]) => {
      payload.append(key, value);
    });
    createImages.value.forEach((file) => payload.append('images[]', file));

    const response = await fetch('/api/products', {
      method: 'POST',
      headers: authHeaders(),
      body: payload,
    });

    const data = await response.json();
    if (!response.ok) {
      error.value = data?.message || data?.errors?.title?.[0] || 'Erro ao cadastrar produto.';
      return;
    }

    success.value = 'Produto cadastrado com sucesso!';
    createForm.value = { title: '', description: '', sale_price: 0, cost: 0 };
    createImages.value = [];
    products.value = [...products.value, data].sort((a, b) => a.title.localeCompare(b.title));
  } catch (fetchError) {
    console.error(fetchError);
    error.value = 'Erro ao conectar ao servidor.';
  } finally {
    createLoading.value = false;
  }
};

const startEdit = (product) => {
  resetMessages();
  editingId.value = product.id;
  editForm.value = {
    title: product.title,
    description: product.description || '',
    sale_price: Number(product.sale_price),
    cost: Number(product.cost),
  };
  editImages.value = [];
};

const cancelEdit = () => {
  editingId.value = null;
  editForm.value = { title: '', description: '', sale_price: 0, cost: 0 };
  editImages.value = [];
};

const handleUpdate = async (productId) => {
  resetMessages();
  editLoading.value = true;

  try {
    const payload = new FormData();
    payload.append('_method', 'PUT');
    Object.entries(editForm.value).forEach(([key, value]) => {
      payload.append(key, value);
    });
    editImages.value.forEach((file) => payload.append('images[]', file));

    const response = await fetch(`/api/products/${productId}`, {
      method: 'POST',
      headers: authHeaders(),
      body: payload,
    });

    const data = await response.json();
    if (!response.ok) {
      error.value = data?.message || data?.errors?.title?.[0] || 'Erro ao atualizar produto.';
      return;
    }

    products.value = products.value.map((item) => (item.id === productId ? data : item));
    success.value = 'Produto atualizado com sucesso!';
    cancelEdit();
  } catch (fetchError) {
    console.error(fetchError);
    error.value = 'Erro ao conectar ao servidor.';
  } finally {
    editLoading.value = false;
  }
};

const handleInactivate = async (productId) => {
  resetMessages();

  try {
    const response = await fetch(`/api/products/${productId}/inactivate`, {
      method: 'PATCH',
      headers: {
        ...authHeaders(),
        'Content-Type': 'application/json',
      },
    });

    const data = await response.json();
    if (!response.ok) {
      error.value = data?.message || 'Erro ao inativar produto.';
      return;
    }

    products.value = products.value.map((item) => (item.id === productId ? data : item));
    success.value = 'Produto inativado com sucesso!';
  } catch (fetchError) {
    console.error(fetchError);
    error.value = 'Erro ao conectar ao servidor.';
  }
};

onMounted(async () => {
  const token = getToken();
  if (!token) {
    router.visit('/login');
    return;
  }

  await fetchProducts();
  loading.value = false;
});
</script>

<template>
  <div class="min-h-screen bg-gray-100 py-10 px-4">
    <div class="max-w-5xl mx-auto space-y-6">
      <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Produtos</h1>
          <p class="text-gray-600 mt-1">Gerencie o cadastro e o status dos produtos.</p>
        </div>
        <button
          type="button"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition"
          @click="showCreate = !showCreate"
        >
          {{ showCreate ? 'Ocultar cadastro' : 'Cadastrar produto' }}
        </button>
      </div>

      <div v-if="error" class="p-4 rounded-lg bg-red-100 border border-red-300 text-red-700">
        {{ error }}
      </div>
      <div v-if="success" class="p-4 rounded-lg bg-green-100 border border-green-300 text-green-700">
        {{ success }}
      </div>

      <div v-if="showCreate" class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Cadastrar</h2>
        <form class="grid md:grid-cols-3 gap-4" @submit.prevent="handleCreate">
          <div class="md:col-span-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">Titulo</label>
              <input
                v-model="createForm.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Titulo do produto"
              />
            </div>
            <div class="md:col-span-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">Descricao (HTML)</label>
              <textarea
                v-model="createForm.description"
                rows="2"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Aceita <p>, <br>, <b>, <strong>"
              ></textarea>
            </div>
            <div class="md:col-span-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">Preco de venda</label>
              <input
                v-model.number="createForm.sale_price"
                type="number"
                min="0"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="0.00"
              />
            </div>
            <div class="md:col-span-1">
              <label class="block text-sm font-medium text-gray-700 mb-1">Custo</label>
              <input
                v-model.number="createForm.cost"
                type="number"
                min="0"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="0.00"
              />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Imagens (jpg/png)</label>
              <input
                type="file"
                multiple
                accept="image/png,image/jpeg"
                class="w-full text-sm text-gray-600"
                @change="(event) => { createImages = Array.from(event.target.files || []); }"
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
          <h2 class="text-xl font-semibold text-gray-800">Lista de produtos</h2>
          <p class="text-sm text-gray-500 mt-1">Edite ou inative itens conforme necessario.</p>
        </div>

        <div v-if="loading" class="p-6 text-gray-500">Carregando produtos...</div>
        <div v-else-if="products.length === 0" class="p-6 text-gray-500">Nenhum produto cadastrado.</div>

        <div v-else class="divide-y divide-gray-200">
          <div
            v-for="product in products"
            :key="product.id"
            class="p-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
          >
            <div class="flex-1">
              <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-gray-900">{{ product.title }}</h3>
                <span
                  class="text-xs font-semibold px-2 py-1 rounded-full"
                  :class="product.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'"
                >
                  {{ product.is_active ? 'Ativo' : 'Inativo' }}
                </span>
              </div>
              <div
                class="text-sm text-gray-600 mt-1 prose prose-sm max-w-none"
                v-html="product.description || 'Sem descricao'"
              ></div>
            </div>
            <div class="flex items-center gap-6">
              <div class="flex items-center gap-2">
                <img
                  v-for="(url, idx) in (product.image_urls || []).slice(0, 3)"
                  :key="`${product.id}-img-${idx}`"
                  :src="url"
                  alt=""
                  class="w-12 h-12 rounded-lg object-cover border border-gray-200"
                />
                <span v-if="(product.image_urls || []).length === 0" class="text-xs text-gray-400">
                  Sem imagens
                </span>
              </div>
              <div class="text-right">
                <p class="text-sm text-gray-500">Venda</p>
                <p class="text-lg font-semibold text-gray-800">{{ formatPrice(product.sale_price) }}</p>
                <p class="text-xs text-gray-400">Custo: {{ formatPrice(product.cost) }}</p>
              </div>
              <div class="flex gap-2">
                <button
                  type="button"
                  class="px-3 py-2 text-sm font-medium rounded-lg border border-blue-300 text-blue-700 hover:bg-blue-50"
                  @click="startEdit(product)"
                >
                  Editar
                </button>
                <button
                  type="button"
                  class="px-3 py-2 text-sm font-medium rounded-lg border border-red-300 text-red-700 hover:bg-red-50 disabled:opacity-50"
                  :disabled="!product.is_active"
                  @click="handleInactivate(product.id)"
                >
                  Inativar
                </button>
              </div>
            </div>

            <div v-if="editingId === product.id" class="w-full bg-gray-50 rounded-lg p-4">
              <h4 class="text-sm font-semibold text-gray-700 mb-3">Editar produto</h4>
              <form class="grid md:grid-cols-3 gap-4" @submit.prevent="handleUpdate(product.id)">
                <div class="md:col-span-1">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Titulo</label>
                  <input
                    v-model="editForm.title"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div class="md:col-span-1">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Descricao (HTML)</label>
                  <textarea
                    v-model="editForm.description"
                    rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  ></textarea>
                </div>
                <div class="md:col-span-1">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Preco de venda</label>
                  <input
                    v-model.number="editForm.sale_price"
                    type="number"
                    min="0"
                    step="0.01"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div class="md:col-span-1">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Custo</label>
                  <input
                    v-model.number="editForm.cost"
                    type="number"
                    min="0"
                    step="0.01"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Novas imagens (jpg/png)</label>
                  <input
                    type="file"
                    multiple
                    accept="image/png,image/jpeg"
                    class="w-full text-sm text-gray-600"
                    @change="(event) => { editImages = Array.from(event.target.files || []); }"
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
