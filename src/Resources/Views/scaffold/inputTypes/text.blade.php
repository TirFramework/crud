
@php
$model = null;
if( isset($item->{$field->name}) ){
    $model = $item->{$field->name};
}
//TODO:remove laravel form generator package
@endphp
<div class="{{$field->col ?? 'col-12 col-md-6'}}">

    <div class="form-group">
            <input type="text"
            id="{{$field->name}}"
            name="{{$field->name}}"
            placeholder="{{$field->placeholder ?? null}}"
            {{$field->option ?? null}}
            value="{{ old( $field->name, $model ) }}"
            class="form-control @error($field->name) is-invalid @enderror"
            {!!$field->validation ?? null!!}
            >
            <span class="invalid-feedback" role="alert">
                @error($field->name)
                <strong>{{ $message }}</strong>
                @enderror
            </span>
            <label for="{{$field->name}}" class="control-label text-right">@lang("$crud->name::panel.$field->display")</label>
    </div>

</div>
