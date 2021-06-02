{{--Submit & Cancel--}}
<div class="w-100">
    <div class="form-group text-right m-0">
        <div class="row row-margin-5">
            {{-- {!! Form::label('', '', ['class' => 'col-md-2 control-label']) !!} --}}
            <div class="col-auto">
                {!!  Form::submit(trans('crud::panel.update_close'),['name'=>'save_close','class'=>'btn btn-success save_close'])!!}
            </div>
            <div class="col-auto">
                {!!  Form::submit(trans('crud::panel.update'),['name'=>'save_edit','class'=>'btn btn-info save'])!!}
            </div>
            {{-- @if(App\Modules\Authorization\Acl::checkAccess($crud->name, 'index')) --}}
            <div class="col-auto">
                <a type="button" href="{{route("admin.$crud->name.index")}}"
                   class="btn btn-warning">{{trans('crud::panel.cancel')}}</a>
            </div>
            {{-- @else --}}
            {{-- <a type="button" href="{{route("dashboard")}}" class="btn btn-warning">{{trans('crud::panel.cancel')}}</a> --}}
            {{-- @endif --}}
        </div>
    </div>
</div>

