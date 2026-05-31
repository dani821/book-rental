<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';
import { LoaderCircle } from '@lucide/vue';

import Alert from '@/components/common/Alert.vue';
import FormField from '@/components/forms/FormField.vue';
import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { useApiError } from '@/composables/useApiError';
import { BOOK_GENRES, type Book, type BookFormPayload, type BookGenre } from '@/types/book';

const props = defineProps<{
    /** The book to edit, or null/undefined to create a new one. */
    book?: Book | null;
    /** Provided by the parent; performs the create/update through the books composable. */
    submit: (payload: BookFormPayload) => Promise<unknown>;
}>();

const open = defineModel<boolean>('open', { required: true });
const emit = defineEmits<{ saved: [isEdit: boolean] }>();

const { fieldErrors, generalMessage, setFromError, clearField, clear } = useApiError();

const isEdit = computed(() => Boolean(props.book));
const submitting = ref(false);

const form = reactive({
    title: '',
    author: '',
    genre: 'fiction' as BookGenre,
    isbn: '',
    published_year: '',
    total_pages: '',
    total_copies: '1',
});

const genreModel = computed({
    get: () => form.genre,
    set: (value: string) => {
        form.genre = value as BookGenre;
    },
});

watch(open, (isOpen) => {
    if (!isOpen) {
        return;
    }

    clear();
    const book = props.book;
    form.title = book?.title ?? '';
    form.author = book?.author ?? '';
    form.genre = book?.genre ?? 'fiction';
    form.isbn = book?.isbn ?? '';
    form.published_year = book?.publishedYear != null ? String(book.publishedYear) : '';
    form.total_pages = book ? String(book.totalPages) : '';
    form.total_copies = book ? String(book.totalCopies) : '1';
});

function buildPayload(): BookFormPayload {
    const publishedYear = form.published_year.trim();
    const isbn = form.isbn.trim();

    return {
        title: form.title.trim(),
        author: form.author.trim(),
        genre: form.genre,
        isbn: isbn === '' ? null : isbn,
        published_year: publishedYear === '' ? null : Number(publishedYear),
        total_pages: Number(form.total_pages),
        total_copies: Number(form.total_copies),
    };
}

async function onSubmit(): Promise<void> {
    submitting.value = true;
    clear();

    try {
        await props.submit(buildPayload());
        emit('saved', isEdit.value);
        open.value = false;
    } catch (error) {
        setFromError(error);
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <Dialog
        v-model:open="open"
        :title="isEdit ? 'Edit book' : 'New book'"
        :description="isEdit ? 'Update the details for this title.' : 'Add a new title to the catalogue.'"
    >
        <form id="book-form" class="grid gap-4" novalidate @submit.prevent="onSubmit">
            <Alert v-if="generalMessage">{{ generalMessage }}</Alert>

            <FormField v-model="form.title" label="Title" required :error="fieldErrors.title" @update:model-value="clearField('title')" />

            <FormField v-model="form.author" label="Author" required :error="fieldErrors.author" @update:model-value="clearField('author')" />

            <div class="grid gap-2">
                <Label required>Genre</Label>
                <Select v-model="genreModel" aria-label="Genre" :class="fieldErrors.genre ? 'border-destructive' : undefined">
                    <option v-for="option in BOOK_GENRES" :key="option.value" :value="option.value">{{ option.label }}</option>
                </Select>
                <p v-if="fieldErrors.genre" class="text-sm text-destructive">{{ fieldErrors.genre }}</p>
            </div>

            <FormField v-model="form.isbn" label="ISBN" placeholder="Optional" :error="fieldErrors.isbn" @update:model-value="clearField('isbn')" />

            <div class="grid items-start gap-4 sm:grid-cols-2">
                <FormField
                    v-model="form.published_year"
                    label="Published year"
                    type="number"
                    placeholder="Optional"
                    :error="fieldErrors.published_year"
                    @update:model-value="clearField('published_year')"
                />
                <FormField
                    v-model="form.total_pages"
                    label="Total pages"
                    type="number"
                    required
                    :error="fieldErrors.total_pages"
                    @update:model-value="clearField('total_pages')"
                />
            </div>

            <FormField
                v-model="form.total_copies"
                label="Total copies"
                type="number"
                required
                :error="fieldErrors.total_copies"
                @update:model-value="clearField('total_copies')"
            />
        </form>

        <template #footer>
            <Button variant="outline" :disabled="submitting" @click="open = false">Cancel</Button>
            <Button type="submit" form="book-form" :disabled="submitting">
                <LoaderCircle v-if="submitting" class="animate-spin" />
                {{ isEdit ? 'Save changes' : 'Create book' }}
            </Button>
        </template>
    </Dialog>
</template>
