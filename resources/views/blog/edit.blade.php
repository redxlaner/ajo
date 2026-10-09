<x-app-layout>
    @php $editing = $post->exists; @endphp
    <form method="POST" action="{{ $editing ? route('blog.update', $post) : route('blog.store') }}" enctype="multipart/form-data" class="max-w-2xl mx-auto px-4 py-8 space-y-4">
        @csrf
        @if($editing) @method('PUT') @endif
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $editing ? 'Edit guide' : 'New guide' }}</h1>
        <input name="title" value="{{ old('title', $post->title) }}" required placeholder="Title" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
        <select name="category" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
            @foreach(\App\Models\BlogPost::categories() as $key => $label)
                <option value="{{ $key }}" @selected(old('category', $post->category) === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <textarea name="excerpt" required maxlength="300" rows="2" placeholder="Short summary" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('excerpt', $post->excerpt) }}</textarea>
        <textarea name="body" required rows="12" placeholder="The guide" class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('body', $post->body) }}</textarea>
        @if($post->cover)
            <img src="{{ asset('storage/'.$post->cover) }}" alt="" class="h-28 rounded-xl object-cover">
        @endif
        <input type="file" name="cover" accept="image/*">
        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="publish" value="1" @checked(old('publish', (bool) $post->published_at))> Publish
        </label>
        <button class="bg-indigo-600 text-white font-medium px-5 py-2.5 rounded-xl">Save</button>
        @if($errors->any())
            <p class="text-sm text-rose-600">{{ $errors->first() }}</p>
        @endif
    </form>
</x-app-layout>