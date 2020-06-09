@php
    //set #id for jquery confilict
    $idTag = preg_replace("/[^a-zA-Z_]/", "", $field->name);
    $fieldName = $field->name;
    $options = ['id'=> "select-$idTag", 'class'=>'dropdown-toggle form-control'];

    //when we are in create page set $item to null for undefined variable error
    if(!isset($item)){
        $item = null;
    }

    $options ['placeholder'] = trans("$crud->name::panel.select").' '.trans("$crud->name::panel.$field->display");

     $model =   get_class($crud->model->{$field->relation[0]}()->getModel());
    $key = $field->relation[1];
    $model = new $model;

    //get primary key from model that most often is id;
    $id = $model->getKeyName();  

    if (in_array($key, $model->translatedAttributes)){
        $values = $model::select('*')->get()->pluck($key,$id);
    }else{
        if(isset($item->{$field->name})){
             $values = $model::select($key,$id)->where($id,$item->{$field->name})->pluck($key,$id);
        }else{
             $values = [];
        }
    }

@endphp

<div class="{{$field->col ?? 'col-12 col-md-12'}}">
    <div class="form-group">
        {!! Form::select($fieldName, $values, null, $options)!!}
        {!! Form::label($fieldName,trans("$crud->name::panel.$field->display"), ['class' => 'control-label']) !!}
    </div>
</div>

@push('scripts')
    <script>
    $("#select-{{$idTag}}").select2({
        placeholder: "{{$options ['placeholder']}}",
        dir: $('body').attr('dir'),
        allowClear: true,
        ajax: {
            // url: "/admin/{{$model::$routeName}}/select/?key={{$key}}",
            url: "{{ route($model::$routeName.'.select') }}/?key={{$key}}",
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
