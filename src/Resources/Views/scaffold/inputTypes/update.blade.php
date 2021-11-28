{{--Submit & Cancel--}}
<div class="w-100">
    <div class="form-group text-right m-0">
        <div class="row row-margin-5">
            <div class="col-auto">
                {!!  Form::submit(trans('core::panel.update_close'),['name'=>'save_close','class'=>'btn btn-success save_close'])!!}
            </div>
            <div class="col-auto">
                {!!  Form::submit(trans('core::panel.update'),['name'=>'save_edit','class'=>'btn btn-info save'])!!}
            </div>
            {{-- @if(App\Modules\Authorization\Acl::checkAccess($crud->name, 'index')) --}}
            <div class="col-auto">
                <a type="button" href="{{route('admin.'.$model->getModuleName().'.index')}}"
                   class="btn btn-warning">{{trans('core::panel.cancel')}}</a>
            </div>
        </div>
    </div>
</div>

