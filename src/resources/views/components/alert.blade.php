@if (session('notification'))
    <div class="alert alert-{{ session('notification.type') }} mt-2" role="alert">
        {{ session('notification.text') }}
    </div>
@endif