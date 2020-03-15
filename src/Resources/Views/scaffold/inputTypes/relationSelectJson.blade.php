@php
    $fieldName = $field->name;
    $options = ['id'=> "select-$field->name", 'class'=>' dropdown-toggle form-control'];
    if(isset($field->multiple)):
        if( $field->multiple == true):
            $options['multiple'] = 'multiple';
            $fieldName = $field->name.'[]';
        endif;
     else:
     $options ['placeholder'] = trans('panel.select').' '.trans("$crud->name::panel.$field->display");
    endif;

     $model = $field->data;
     $key = 'title';

    if(is_array($field->data)){
        if(isset($field->data[0])){
            $model = $field->data[0];
        }
        if(isset($field->data[1])){
            $key = $field->data[1];
        }
    }
    $loadModel = 'App\Models\\'.$model;
    $values = $loadModel::pluck($key,'id');
@endphp

<div class="form-group">
    {!! Form::label($fieldName,trans("$crud->name::panel.$field->display"), ['class' => 'col-md-2 control-label']) !!}
    <div class="col-md-10">
        {!! Form::select($fieldName, $values, null,$options)!!}
    </div>
</div>

@push('scripts')
    <script>
    $("#select-{{$field->name}}").select2({
        placeholder: "{{trans('panel.select').' '.trans("$crud->name::panel.$field->display")}}",
        ajax: {
            url: '/admin/{{strtolower($model)}}/select/?key={{$key}}',
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term
                }
                return query;
            }
        }
    });
</script>
@endpush
