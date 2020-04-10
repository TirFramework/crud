
<div class="{{$field->col ?? 'col-12 col-md-12'}}">
    <div class="form-group">
        <div class="input-group">
            {!! Form::file($field->name, null,['class' => 'form-control'])!!}
        </div>
        {!! Form::label("$field->name", trans("$crud->name::panel.$field->display"), ['class' => 'control-label']) !!}
        <img id="{{$field->name}}_holder" @isset($item->{$field->name}) src="{{ $item->{$field->name} }}" @endisset class="image-holder">
    </div>
</div>
