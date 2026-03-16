<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const loading = ref(true);
const products = ref([]);
const createErrorMessages = ref([]);
const editErrorMessages = ref([]);
const success = ref(null);

const showCreate = ref(true);
const createLoading = ref(false);
const editLoading = ref(false);
const showUserMenu = ref(false);

const createForm = ref({
  title: '',
  description: '',
  sale_price: 0,
  cost: 0,
});

const createImages = ref([]);
const createImagePreviews = ref([]);
const createFileInput = ref(null);

const editingId = ref(null);
const editForm = ref({
  title: '',
  description: '',
  sale_price: 0,
  cost: 0,
});

const editImages = ref([]);
const editImagePreviews = ref([]);
const editImageOrder = ref([]);
const editDraggingId = ref(null);
const editFileInput = ref(null);

const getToken = () => localStorage.getItem('auth_token');
const getStoredUser = () => {
  try {
    const raw = localStorage.getItem('user');
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
};

const authHeaders = () => ({
  Authorization: `Bearer ${getToken()}`,
  Accept: 'application/json',
});

const isAdminUser = () => {
  const user = getStoredUser();
  return user?.id === 1;
};

const logout = async () => {
  const token = getToken();
  if (token) {
    try {
      await fetch('/api/logout', {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${token}`,
          Accept: 'application/json',
        },
      });
    } catch (error) {
      console.error(error);
    }
  }

  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  router.visit('/login');
};

const setErrorsFromResponse = (data, fallback, target) => {
  if (data?.errors) {
    const flattened = Object.values(data.errors).flat().filter(Boolean);
    target.value = flattened.length > 0 ? flattened : [fallback];
    return;
  }
  if (data?.message) {
    target.value = [data.message];
    return;
  }
  target.value = [fallback];
};

const formatPrice = (value) => {
  if (value === null || value === undefined) return 'R$ 0,00';
  const numberValue = Number(value);
  if (Number.isNaN(numberValue)) return 'R$ 0,00';
  return numberValue.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
};

const normalizeCollection = (payload) => (Array.isArray(payload?.data) ? payload.data : payload || []);
const normalizeItem = (payload) => (payload?.data ? payload.data : payload);

const fetchProducts = async () => {
  createErrorMessages.value = [];
  editErrorMessages.value = [];
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
      setErrorsFromResponse(data, 'Erro ao carregar produtos.', createErrorMessages);
      return;
    }

    products.value = normalizeCollection(data);
  } catch (fetchError) {
    console.error(fetchError);
    createErrorMessages.value = ['Erro ao conectar ao servidor.'];
  }
};

const resetMessages = () => {
  createErrorMessages.value = [];
  editErrorMessages.value = [];
  success.value = null;
};

const buildPreviews = (files) => files.map((file) => URL.createObjectURL(file));

const revokePreviews = (urls) => {
  urls.forEach((url) => URL.revokeObjectURL(url));
};

const onCreateFilesChange = (event) => {
  revokePreviews(createImagePreviews.value);
  const files = Array.from(event.target.files || []);
  createImages.value = files;
  createImagePreviews.value = buildPreviews(files);
};

const onEditFilesChange = (event) => {
  revokePreviews(editImagePreviews.value);
  const files = Array.from(event.target.files || []);
  editImages.value = files;
  editImagePreviews.value = buildPreviews(files);
};

const handleCreate = async () => {
  resetMessages();
  createLoading.value = true;

  try {
    const payload = new FormData();
    Object.entries(createForm.value).forEach(([key, value]) => {
      payload.append(key, value);
    });
    const createFiles = createImages.value.length
      ? createImages.value
      : Array.from(createFileInput.value?.files || []);
    createFiles.forEach((file) => payload.append('images[]', file));

    const response = await fetch('/api/products', {
      method: 'POST',
      headers: authHeaders(),
      body: payload,
    });

    const data = await response.json();
    if (!response.ok) {
      setErrorsFromResponse(data, 'Erro ao cadastrar produto.', createErrorMessages);
      return;
    }

    const createdProduct = normalizeItem(data);
    success.value = 'Produto cadastrado com sucesso!';
    createForm.value = { title: '', description: '', sale_price: 0, cost: 0 };
    revokePreviews(createImagePreviews.value);
    createImages.value = [];
    createImagePreviews.value = [];
    if (createFileInput.value) {
      createFileInput.value.value = '';
    }
    products.value = [...products.value, createdProduct].sort((a, b) => a.title.localeCompare(b.title));
  } catch (fetchError) {
    console.error(fetchError);
    createErrorMessages.value = ['Erro ao conectar ao servidor.'];
  } finally {
    createLoading.value = false;
  }
};

const startEdit = (product) => {
  resetMessages();
  editingId.value = editingId.value === product.id ? null : product.id;
  if (editingId.value === null) {
    return;
  }
  editForm.value = {
    title: product.title,
    description: product.description || '',
    sale_price: Number(product.sale_price),
    cost: Number(product.cost),
  };
  editImages.value = [];
  editImageOrder.value = Array.isArray(product.image_items) ? [...product.image_items] : [];
};

const cancelEdit = () => {
  editingId.value = null;
  editForm.value = { title: '', description: '', sale_price: 0, cost: 0 };
  editImages.value = [];
  revokePreviews(editImagePreviews.value);
  editImagePreviews.value = [];
  editImageOrder.value = [];
  editDraggingId.value = null;
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
    const editFiles = editImages.value.length
      ? editImages.value
      : Array.from(editFileInput.value?.files || []);
    editFiles.forEach((file) => payload.append('images[]', file));

    const response = await fetch(`/api/products/${productId}`, {
      method: 'POST',
      headers: authHeaders(),
      body: payload,
    });

    const data = await response.json();
    if (!response.ok) {
      setErrorsFromResponse(data, 'Erro ao atualizar produto.', editErrorMessages);
      return;
    }

    const updatedProduct = normalizeItem(data);
    products.value = products.value.map((item) => (item.id === productId ? updatedProduct : item));
    editImageOrder.value = Array.isArray(updatedProduct.image_items) ? [...updatedProduct.image_items] : [];
    success.value = 'Produto atualizado com sucesso!';
    cancelEdit();
  } catch (fetchError) {
    console.error(fetchError);
    editErrorMessages.value = ['Erro ao conectar ao servidor.'];
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
      setErrorsFromResponse(data, 'Erro ao inativar produto.', editErrorMessages);
      return;
    }

    const inactivatedProduct = normalizeItem(data);
    products.value = products.value.map((item) => (item.id === productId ? inactivatedProduct : item));
    success.value = 'Produto inativado com sucesso!';
  } catch (fetchError) {
    console.error(fetchError);
    editErrorMessages.value = ['Erro ao conectar ao servidor.'];
  }
};

const handleDeleteImage = async (productId, imageId) => {
  resetMessages();

  try {
    const response = await fetch(`/api/products/${productId}/images/${imageId}`, {
      method: 'DELETE',
      headers: authHeaders(),
    });

    const data = await response.json();
    if (!response.ok) {
      setErrorsFromResponse(data, 'Erro ao remover imagem.', editErrorMessages);
      return;
    }

    const updatedProduct = normalizeItem(data);
    products.value = products.value.map((item) => (item.id === productId ? updatedProduct : item));
    editImageOrder.value = Array.isArray(updatedProduct.image_items) ? [...updatedProduct.image_items] : [];
    success.value = 'Imagem removida com sucesso!';
  } catch (fetchError) {
    console.error(fetchError);
    editErrorMessages.value = ['Erro ao conectar ao servidor.'];
  }
};

const reorderArray = (list, fromId, toId) => {
  const fromIndex = list.findIndex((item) => item.id === fromId);
  const toIndex = list.findIndex((item) => item.id === toId);
  if (fromIndex === -1 || toIndex === -1 || fromIndex === toIndex) return list;
  const updated = [...list];
  const [moved] = updated.splice(fromIndex, 1);
  updated.splice(toIndex, 0, moved);
  return updated;
};

const handleDragStart = (imageId) => {
  editDraggingId.value = imageId;
};

const handleDrop = async (productId, targetId) => {
  const draggedId = editDraggingId.value;
  if (!draggedId || draggedId === targetId) return;
  editDraggingId.value = null;

  const nextOrder = reorderArray(editImageOrder.value, draggedId, targetId);
  editImageOrder.value = nextOrder;

  try {
    const response = await fetch(`/api/products/${productId}/images/reorder`, {
      method: 'PATCH',
      headers: {
        ...authHeaders(),
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ order: nextOrder.map((item) => item.id) }),
    });

    const data = await response.json();
    if (!response.ok) {
      setErrorsFromResponse(data, 'Erro ao reordenar imagens.', editErrorMessages);
      return;
    }

    const reorderedProduct = normalizeItem(data);
    products.value = products.value.map((item) => (item.id === productId ? reorderedProduct : item));
    editImageOrder.value = Array.isArray(reorderedProduct.image_items) ? [...reorderedProduct.image_items] : [];
    success.value = 'Imagens reordenadas!';
  } catch (fetchError) {
    console.error(fetchError);
    editErrorMessages.value = ['Erro ao conectar ao servidor.'];
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
  <div class="min-h-screen bg-[#131314] py-10 px-4">
    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex items-start justify-between gap-4 flex-wrap">
          <div>
            <p class="text-lg font-bold tracking-[0.2em] text-white">STOCKMASTER</p>
            <h1 class="text-3xl font-semibold text-white mt-2">Produtos</h1>
            <p class="text-gray-400 mt-1">Gerencie o cadastro e o status dos produtos.</p>
          </div>
        <div class="flex items-center gap-2">
          <div v-if="isAdminUser()" class="relative">
            <button
              type="button"
              class="px-4 py-2 border border-zinc-700 text-gray-200 rounded-lg font-medium hover:bg-zinc-900 transition"
              @click="showUserMenu = !showUserMenu"
            >
              Conta
            </button>
            <div
              v-if="showUserMenu"
              class="absolute right-0 mt-2 w-52 rounded-lg border border-zinc-800 bg-zinc-950 shadow-xl z-10"
            >
              <Link
                :href="route('users')"
                class="block px-4 py-2 text-sm text-gray-200 hover:bg-zinc-900"
                @click="showUserMenu = false"
              >
                Usuarios
              </Link>
              <button
                type="button"
                class="block w-full px-4 py-2 text-left text-sm text-gray-200 hover:bg-zinc-900"
                @click="logout"
              >
                Sair
              </button>
            </div>
          </div>
          <button
            v-else
            type="button"
            class="px-4 py-2 border border-zinc-700 text-gray-200 rounded-lg font-medium hover:bg-zinc-900 transition"
            @click="logout"
          >
            Sair
          </button>
          <button
            type="button"
            class="px-4 py-2 bg-lime-500 text-black rounded-lg font-semibold hover:bg-lime-400 transition"
            @click="showCreate = !showCreate"
          >
            {{ showCreate ? 'Ocultar cadastro' : 'Cadastrar produto' }}
          </button>
        </div>
      </div>

      <div
        v-if="createErrorMessages.length"
        class="p-4 rounded-lg bg-red-500/10 border border-red-500/40 text-red-200"
      >
        <ul class="list-disc pl-5 space-y-1">
          <li v-for="(message, idx) in createErrorMessages" :key="`err-create-${idx}`" class="text-sm">
            {{ message }}
          </li>
        </ul>
      </div>
      <div v-if="success" class="p-4 rounded-lg bg-lime-500/10 border border-lime-500/40 text-lime-200">
        {{ success }}
      </div>

      <div v-if="showCreate" class="bg-zinc-950 rounded-2xl border border-zinc-900 shadow-[0_20px_50px_-35px_rgba(0,0,0,0.8)] p-6">
        <h2 class="text-xl font-semibold text-white mb-4">Cadastrar</h2>
        <form class="grid md:grid-cols-2 gap-4" @submit.prevent="handleCreate">
          <div class="md:col-span-1">
              <label class="block text-sm font-medium text-gray-300 mb-1">Titulo</label>
              <input
                v-model="createForm.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                placeholder="Titulo do produto"
              />
            </div>
            <div class="md:col-span-1">
              <label class="block text-sm font-medium text-gray-300 mb-1">Descricao</label>
              <input
                v-model="createForm.description"
                type="text"
                class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                placeholder="Descricao do produto"
              />
            </div>
            <div class="md:col-span-1">
              <label class="block text-sm font-medium text-gray-300 mb-1">Preco de venda</label>
              <input
                v-model.number="createForm.sale_price"
                type="number"
                min="0"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                placeholder="0.00"
              />
            </div>
            <div class="md:col-span-1">
              <label class="block text-sm font-medium text-gray-300 mb-1">Custo</label>
              <input
                v-model.number="createForm.cost"
                type="number"
                min="0"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                placeholder="0.00"
              />
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-300 mb-1">Imagens (jpg/png)</label>
              <input
                type="file"
                multiple
                accept="image/png,image/jpeg"
                class="w-full text-sm text-gray-300 file:mr-4 file:rounded-lg file:border-0 file:bg-lime-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-black hover:file:bg-lime-400"
                ref="createFileInput"
                @change="onCreateFilesChange"
                @input="onCreateFilesChange"
              />
              <div v-if="createImagePreviews.length" class="mt-3 flex flex-wrap gap-2">
                <img
                  v-for="(url, idx) in createImagePreviews"
                  :key="`create-preview-${idx}`"
                  :src="url"
                  alt=""
                  class="w-14 h-14 rounded-lg object-cover border border-zinc-700"
                />
              </div>
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

      <div
        v-if="editErrorMessages.length"
        class="p-4 rounded-lg bg-red-500/10 border border-red-500/40 text-red-200"
      >
        <ul class="list-disc pl-5 space-y-1">
          <li v-for="(message, idx) in editErrorMessages" :key="`err-edit-${idx}`" class="text-sm">
            {{ message }}
          </li>
        </ul>
      </div>

      <div class="bg-zinc-950 rounded-2xl border border-zinc-900 shadow-[0_20px_50px_-35px_rgba(0,0,0,0.8)]">
        <div class="p-6 border-b border-zinc-900">
          <h2 class="text-xl font-semibold text-white">Lista de produtos</h2>
          <p class="text-sm text-gray-400 mt-1">Edite ou inative itens conforme necessario.</p>
        </div>

        <div v-if="loading" class="p-6 text-gray-400">Carregando produtos...</div>
        <div v-else-if="products.length === 0" class="p-6 text-gray-400">Nenhum produto cadastrado.</div>

        <div v-else class="divide-y divide-zinc-900">
          <div
            v-for="product in products"
            :key="product.id"
            class="p-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
          >
            <div class="flex-1">
              <div class="flex items-center gap-3">
                <h3 class="text-lg font-semibold text-white">{{ product.title }}</h3>
                <span
                  class="text-xs font-semibold px-2 py-1 rounded-full"
                  :class="product.is_active ? 'bg-lime-500/20 text-lime-200' : 'bg-zinc-800 text-gray-400'"
                >
                  {{ product.is_active ? 'Ativo' : 'Inativo' }}
                </span>
              </div>
              <div
                class="text-sm text-gray-400 mt-1 prose prose-sm max-w-none"
                v-html="product.description || 'Sem descricao'"
              ></div>
            </div>
            <div v-if="editingId !== product.id" class="flex items-center gap-6">
              <div class="flex items-center gap-2">
                <img
                  v-for="(url, idx) in (product.image_urls || []).slice(0, 3)"
                  :key="`${product.id}-img-${idx}`"
                  :src="url"
                  alt=""
                  class="w-12 h-12 rounded-lg object-cover border border-zinc-700"
                />
                <span v-if="(product.image_urls || []).length === 0" class="text-xs text-gray-400">
                  Sem imagens
                </span>
              </div>
              <div class="text-right">
                <p class="text-lg font-semibold text-white">{{ formatPrice(product.sale_price) }}</p>
                <p class="text-xs text-gray-400">Custo: {{ formatPrice(product.cost) }}</p>
              </div>
              <div class="flex gap-2">
                <button
                  type="button"
                  class="px-3 py-2 text-sm font-medium rounded-lg border border-lime-500/40 text-lime-200 hover:bg-lime-500/10"
                  @click="startEdit(product)"
                >
                  Editar
                </button>
                <button
                  type="button"
                  class="px-3 py-2 text-sm font-medium rounded-lg border border-red-500/40 text-red-200 hover:bg-red-500/10 disabled:opacity-50"
                  :disabled="!product.is_active"
                  @click="handleInactivate(product.id)"
                >
                  Inativar
                </button>
              </div>
            </div>

            <div v-if="editingId === product.id" class="w-full bg-zinc-900/60 rounded-lg p-4 border border-zinc-800">
              <h4 class="text-sm font-semibold text-gray-200 mb-3">Editar produto</h4>
              <form class="grid md:grid-cols-2 gap-4" @submit.prevent="handleUpdate(product.id)">
                <div class="md:col-span-1">
                  <label class="block text-sm font-medium text-gray-300 mb-1">Titulo</label>
                  <input
                    v-model="editForm.title"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                  />
                </div>
                <div class="md:col-span-1">
                  <label class="block text-sm font-medium text-gray-300 mb-1">Descricao</label>
                  <input
                    v-model="editForm.description"
                    type="text"
                    class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                    placeholder="Descricao do produto"
                  />
                </div>
                <div class="md:col-span-1">
                  <label class="block text-sm font-medium text-gray-300 mb-1">Preco de venda</label>
                  <input
                    v-model.number="editForm.sale_price"
                    type="number"
                    min="0"
                    step="0.01"
                    required
                    class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                  />
                </div>
                <div class="md:col-span-1">
                  <label class="block text-sm font-medium text-gray-300 mb-1">Custo</label>
                  <input
                    v-model.number="editForm.cost"
                    type="number"
                    min="0"
                    step="0.01"
                    required
                    class="w-full px-3 py-2 border border-zinc-800 bg-zinc-900 rounded-lg text-gray-100 focus:ring-2 focus:ring-lime-500 focus:border-lime-500"
                  />
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-300 mb-1">Novas imagens (jpg/png)</label>
                  <input
                    type="file"
                    multiple
                    accept="image/png,image/jpeg"
                    class="w-full text-sm text-gray-300 file:mr-4 file:rounded-lg file:border-0 file:bg-lime-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-black hover:file:bg-lime-400"
                    ref="editFileInput"
                    @change="onEditFilesChange"
                    @input="onEditFilesChange"
                  />
                  <div v-if="editImagePreviews.length" class="mt-3 flex flex-wrap gap-2">
                    <img
                      v-for="(url, idx) in editImagePreviews"
                      :key="`edit-preview-${idx}`"
                      :src="url"
                      alt=""
                      class="w-14 h-14 rounded-lg object-cover border border-zinc-700"
                    />
                  </div>
                  <div v-if="editImageOrder.length" class="mt-3">
                    <p class="text-xs text-gray-500 mb-2">
                      Imagens atuais (arraste para reordenar)
                    </p>
                    <div class="flex flex-wrap gap-2">
                      <div
                        v-for="image in editImageOrder"
                        :key="`edit-existing-${product.id}-${image.id}`"
                        class="relative group"
                        draggable="true"
                        @dragstart="handleDragStart(image.id)"
                        @dragover.prevent
                        @drop="handleDrop(product.id, image.id)"
                      >
                        <img
                          :src="image.url"
                          alt=""
                          class="w-14 h-14 rounded-lg object-cover border border-zinc-700"
                        />
                        <button
                          type="button"
                          class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-red-500 text-white text-xs opacity-0 group-hover:opacity-100 transition"
                          @click="handleDeleteImage(product.id, image.id)"
                          title="Remover"
                        >
                          X
                        </button>
                      </div>
                    </div>
                  </div>
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
