@if(session('status'))
    <p role="status">{{ session('status') }}</p>
@endif
@if($errors->any())
    <div role="alert">
        <p>Data belum disimpan. Periksa kembali:</p>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
