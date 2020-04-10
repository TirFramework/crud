@extends(config('crud.admin-panel').'::layouts.master')
@section('navbar')
    {{--navbar add here--}}
@endsection

@section('title', trans('crud::panel.create').' '.trans("$crud->name::panel.$crud->name") )

@section('page-heading'){{trans('crud::panel.create').' '.trans("$crud->name::panel.$crud->name")}} @endsection

@section('content')
{!! Form::open(['route' => "$crud->name.store", 'method' => 'POST', 'class'=>'form-horizontal', 'enctype'=>'multipart/form-data']) !!}
    <div class="card card-default">
        <div class="card-header d-flex align-items-center">
            <h3> {{trans('crud::panel.General_fields')}} </h3>

            {{--Submit & Cancel--}}
            @include("crud::scaffold.inputTypes.save",['crud'=>$crud])
        </div>
        <div class="card-body">
            <div class="row">

            @foreach($crud->fields as $field)
                @if(strpos($field->visible, 'c') !== false)
                    @if(!isset($field->display))
                        @php $field->display = $field->name; @endphp
                    @endif
                    @if(view()->exists("$crud->name::admin.inputTypes.$field->type"))
                        @include("$crud->name::admin.inputTypes.$field->type",['field'=>$field,'crud'=>$crud])
                    @else
                        @include("crud::scaffold.inputTypes.$field->type",['field'=>$field,'crud'=>$crud])
                    @endif
                @endif
            @endforeach

            </div>

            {{--Submit & Cancel--}}
            @include("crud::scaffold.inputTypes.save",['crud'=>$crud])
        </div>
    </div>

    {!! Form::close() !!}


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
