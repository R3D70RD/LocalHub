<h1>Modifier la publication</h1>

<form method="post" action="{{ route('publications.update', $publication) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('publications._form', ['publication' => $publication])
</form>
