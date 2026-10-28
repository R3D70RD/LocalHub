<h1>Nouvelle publication</h1>

<form method="post" action="{{ route('publications.store') }}" enctype="multipart/form-data">
    @csrf
    @include('publications._form', ['publication' => null])
</form>
