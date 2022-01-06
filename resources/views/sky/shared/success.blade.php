@if (session('success'))
<div class="alert-messages">
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close"><span aria-hidden="true">&times;</span></button>
        <ul>
            @foreach (session('success') as $successMessage)
                <li><span class="glyphicon glyphicon-ok"></span>{{ $successMessage }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif
