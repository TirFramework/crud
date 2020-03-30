@extends('first-panel::layouts.master')
@section('navbar')
{{--navbar add here--}}
@endsection

@section('title', trans('crud::panel.edit').' '.trans("$crud->name::panel.$crud->name") )

@section('page-heading'){{trans('crud::panel.edit').' '.trans("$crud->name::panel.$crud->name")}} @endsection

@section('content')


{!! Form::model($item, ['route'=>["$crud->name.update",$item->getKey()],'method' => 'put',
        'class'=>'form-horizontal ', 'enctype'=>'multipart/form-data']) !!}




<div class="card card-default">
    <div class="card-header d-flex align-items-center">
        <h3> {{trans('crud::panel.general_fields')}} </h3>

       {{--Submit & Cancel--}}
        @if(view()->exists("$crud->name::admin.inputTypes.update"))
        @include("$crud->name::admin.inputTypes.update",['crud'=>$crud])
        @else
        @include("crud::scaffold.inputTypes.update",['crud'=>$crud])
        @endif
    </div>
    <div class="card-body ">



<div class="row">

        @foreach($crud->fields as $field)
        @if(strpos($field->visible, 'e') !== false)
        @if(!isset($field->display))
        @php $field->display = $field->name; @endphp
        @endif
        {{--check local folder have input or no--}}
        @if(view()->exists("$crud->name::admin.inputTypes.$field->type"))
        @include("$crud->name::admin.inputTypes.$field->type",['field'=>$field,'crud'=>$crud, 'item'=>$item])
        @else
        @include("crud::scaffold.inputTypes.$field->type",['field'=>$field,'crud'=>$crud, 'item'=>$item])
        @endif
        @endif
        @endforeach

</div>

        {{--Submit & Cancel--}}
        @if(view()->exists("$crud->name::admin.inputTypes.update"))
        @include("$crud->name::admin.inputTypes.update",['crud'=>$crud])
        @else
        @include("crud::scaffold.inputTypes.update",['crud'=>$crud])
        @endif
    </div>
</div>
{!! Form::close() !!}




    {{-- Load additional fields --}}
    @foreach ($crud->additionalFields as $aField)
        @if(strpos($aField->visible, 'e') !== false)
            @if(!isset($aField->display))
                @php $aField->display = $aField->name; @endphp
            @endif
            {{--check local folder have input or no--}}
            @if(view()->exists("$crud->name::admin.inputTypes.$aField->type"))
                    @include("$crud->name::admin.inputTypes.$aField->type",['field'=>$aField,'crud'=>$crud, 'item'=>$item])
                @else
                    @include("crud::scaffold.inputTypes.$aField->type",['field'=>$aField,'crud'=>$crud, 'item'=>$item])
            @endif
        @endif
    @endforeach


@endsection


@push('scripts')
<script>
    editor();
    $(".form-horizontal").validate({
        onkeyup: true,
        onfocus: true
    });
</script>
@endpush
