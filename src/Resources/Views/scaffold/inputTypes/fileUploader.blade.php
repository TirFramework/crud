
    <div class="form-group">
        {!! Form::label("$field->name", trans("$crud->name::panel.$field->display"), ['class' => 'col-md-2 control-label']) !!}
        <div class="col-md-10">
            <div class="input-group">
                {!! Form::file($field->name, null,['class' => 'form-control'])!!}
            </div>
            <img id="{{$field->name}}_holder" @isset($item->{$field->name}) src="{{ $item->{$field->name} }}" @endisset class="image-holder">
        </div>
    </div>
