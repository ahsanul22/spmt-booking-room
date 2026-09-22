@props(['headers'])
<table>
    <thead>
        <tr>
            @foreach($headers as $heading)
                <th scope="col">{{ $heading }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>{{ $slot }}</tbody>
</table>
