<form method="POST" action="{{ url()->current() }}" onsubmit="event.preventDefault()">
@csrf
{{ $slot }}
</form>
