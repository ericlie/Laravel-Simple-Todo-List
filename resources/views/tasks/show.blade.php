@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        @include('tasks.card', ['task' => $task, 'class' => 'col-md-12 pb-3'])
    </div>
</div>
@endsection
