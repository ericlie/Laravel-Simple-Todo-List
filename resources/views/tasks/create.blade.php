@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    {{ __('Create A New Task') }}
                    <span class="pull-right">
                        <a href="{{ route('tasks.index') }}" class="btn btn-primary">
                            {{ __('Back') }}
                        </a>
                    </span>
                </div>
                <div class="card-body">
                    {!! Form::model(null, [
                        'url' => route('tasks.store'),
                        'method' => 'POST',
                        'files' => true,
                    ]) !!}
                        {!! Form::cInput('name_of_employee') !!}
                        {!! Form::cInput('email') !!}
                        {!! Form::cFile('email') !!}
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Save') }}</button>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
