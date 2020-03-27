{{--Submit & Cancel--}}
<div class="col-12">
    <div class="form-group text-right">
        {!! Form::label('', '', ['class' => 'col-md-2 control-label']) !!}
            {!!  Form::submit(trans('crud::panel.update_close'),['name'=>'save_close','class'=>'btn btn-success save_close'])!!}
            {!!  Form::submit(trans('crud::panel.update'),['class'=>'btn btn-info save'])!!}
            {{-- @if(App\Modules\Authorization\Acl::checkAccess($crud->name, 'index')) --}}
            <a type="button" href="{{route("$crud->name.index")}}" class="btn btn-warning">{{trans('crud::panel.cancel')}}</a>
            {{-- @else --}}
            {{-- <a type="button" href="{{route("dashboard")}}" class="btn btn-warning">{{trans('crud::panel.cancel')}}</a> --}}
            {{-- @endif --}}
    </div>
</div>

