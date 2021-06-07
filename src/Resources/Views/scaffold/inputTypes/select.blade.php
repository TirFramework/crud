<?php
$fieldName = $field->name;
$options = ['class' => 'select2 input-lg dropdown-toggle form-control', $field->validation ?? null];
if (isset($field->multiple)):
    if ($field->multiple == true):
        $options['multiple'] = 'multiple';
        $fieldName = $field->name . '[]';
    endif;
else:
    $options ['placeholder'] = trans('core::panel.select') . ' ' . trans("$model->moduleName::panel.$field->display");
endif;

?>

<div class="{{  $field->col ?? 'col-12 col-md-12' }}">
    <div class="form-group">
        {!! Form::select($fieldName, $field->data, null,$options)!!}

        <label for="{{$field->name}}"
               class="control-label text-right">@lang("$model->moduleName::panel.$field->display")</label>
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
        //
        // $(".select2.taggable-c").select2({
        //     tags: true,
        //     tokenSeparators: [',', ' ']
        // });
    </script>
@endpush


@isset($field->script)
    @push('scripts')

    <script>
        {!!$field->script!!}
    </script>
    @endpush
@endisset

