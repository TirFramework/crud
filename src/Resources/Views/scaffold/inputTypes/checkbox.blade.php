
@php
$fieldValue = null;
if( isset($item->{$field->name}) ){
    $fieldValue = $item->{$field->name};
}
//TODO:edit checkbox view
@endphp
<div class="{{$field->col ?? 'col-12 col-md-6'}}">

    <div class="form-group">
            <input type="hidden" name="{{$field->name}}" value="0">

            <input type="checkbox"
            id="{{$field->name}}"
            name="{{$field->name}}"
            placeholder="{{$field->placeholder ?? null}}"
            {{$field->option ?? null}}
            @if(old( $field->name, $fieldValue )) checked @endif
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
