<div class="form-group">
    <label>{{ __($label ?? title_case(str_replace('_', ' ', $name))) }}</label>
    {!! Form::text($name, $value, $attributes ?? [ 'class' => 'form-control' ]) !!}
</div>
