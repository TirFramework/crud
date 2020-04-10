@php
    //set #id for jquery confilict
    $id = preg_replace("/[^a-zA-Z_]/", "", $field->name);
    $fieldName = $field->name.'[]';
    $options = ['id'=> "select-$id", 'class'=>'dropdown-toggle form-control','multiple'];




    $model =  $field->data[0];
    $key = $field->data[1];

    $model = new $model;
    if (in_array($key, $model->translatedAttributes)){
        $values = $model::select('*')->get()->pluck($key,'id');
    }else{
        $values = $model::select($key,'id')->where($key,$item->{$field->name})->pluck($key,'id');
    }


@endphp

<div class="{{$field->col ?? 'col-12 col-md-12'}}">
    <div class="form-group">
        {!! Form::select($fieldName, $values, null,$options)!!}
        {!! Form::label($fieldName,trans("$crud->name::panel.$field->display"), ['class' => 'control-label']) !!}
    </div>
</div>

@push('scripts')
    <script>
    $("#select-{{$id}}").select2({
        placeholder: "{{trans('crud::panel.select').' '.trans("$crud->name::panel.$field->display")}}",
        ajax: {
            url: "/admin/{{$model::$routeName}}/select/?key={{$key}}",
            dataType: 'json',
            data: function (params) {
                var query = {
                    search: params.term,
                    page: params.page || 0
                }
                return query;
            }
        }
    });
</script>
@endpush
