<?php
$fieldName = $field->name;

$fieldData = arrayReplaceKey($item->meta->meta_keywords);

$options = ['class'=>'select2 metaKeywords input-lg dropdown-toggle form-control', $field->validation ?? null ];
if(isset($field->multiple)):
    if( $field->multiple == true):
        $options['multiple'] = 'multiple';
        $fieldName = $field->name.'[]';
    endif;
else:
    $options ['placeholder'] = trans('crud::panel.select').' '.trans("$crud->name::panel.$field->display");
endif;

?>

<div class="{{  $field->col ?? 'col-12 col-md-12' }}">
    <div class="form-group">
            {!! Form::select($fieldName, $fieldData, null,$options)!!}

        <label for="{{$field->name}}" class="control-label text-right">@lang("$crud->name::panel.$field->display")</label>
    </div>
</div>


@push('scripts')
    <script>
        $('[name="{{$field->name}}"').select2({
            placeholder: "{{$field->placeholder ?? null}}",
            allowClear: true,
            dir: $('body').attr('dir'),
            // allowClear: true,
        });

        $(".select2.metaKeywords").select2({
            tags: true,
            tokenSeparators: [',']
        });
    </script>
@endpush


@isset($field->script)
    @push('scripts')

    <script>
        {!!$field->script!!}
    </script>
    @endpush
@endisset

<?php

/*for form select data we need remove array index and replace with value */
function arrayReplaceKey($data) {
    $newArray= [];
    foreach ($data as $key => $value){
        $newArray[$value]=$value;
    }
    return $newArray;
}

