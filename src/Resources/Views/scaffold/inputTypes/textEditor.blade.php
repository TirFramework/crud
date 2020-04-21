@php
    $model = null;
    if( isset($item->{$field->name}) ){
        $model = $item->{$field->name};
    }
@endphp
<div class="{{$field->col ?? 'col-12 col-md-12'}}">

    <div class="form-group">
            <textarea
                   id="{{$field->name}}"
                   name="{{$field->name}}"
                   placeholder="{{$field->placeholder ?? null}}"
                   {{$field->option ?? null}}
                   class="form-control textarea @error($field->name) is-invalid @enderror"
                    {!!$field->validation ?? null!!}
            >{!! old( $field->name, $model ) !!}</textarea>


        <span class="invalid-feedback" role="alert">
                @error($field->name)
                <strong>{{ $message }}</strong>
                @enderror
            </span>
        <label for="{{$field->name}}" class="control-label text-right">@lang("$crud->name::panel.$field->display")</label>
    </div>
</div>