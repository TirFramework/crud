@php
$model = null;
if( isset($item->{$field->name}) ){
$model = $item->{$field->name};
}
@endphp

<div class="{{$field->col ?? 'col-12 col-md-6'}}">
    <div class="form-group">

            @empty($item)
            {{-- {!! Form::password($field->name,['class' => 'form-control'])!!} --}}

            <input type="password" id="{{$field->name}}" name="{{$field->name}}" placeholder="{{$field->placeholder ?? null}}" {{$field->option ?? null}} value="{{ old( $field->name, $model ) }}" class="form-control @error($field->name) is-invalid @enderror" {!!$field->validation ?? null!!} >
            @endempty
            <span class="invalid-feedback" role="alert">
                @error($field->name)
                <strong>{{ $message }}</strong>
                @enderror
            </span>
            @isset($item)
            <div class="form-control clone-password"></div>
            @endisset


            <label for="{{$field->name}}" class="control-label text-right">@lang("$crud->name::panel.$field->display")
                @isset($item)
                <a href="#" class="edit-password"> <i class="fas fa-pen"></i> </a>
                @endisset
            </label>
    </div>
</div>


@isset($item)
@push('scripts')
<script>
    $('.edit-password').click(function(){
        $(this).hide();
        $('.clone-password').hide().after(`<input type="password" id="{{$field->name}}" name="{{$field->name}}" placeholder="{{$field->placeholder ?? null}}" {{$field->option ?? null}} class="form-control @error($field->name) is-invalid @enderror" {!!$field->validation ?? null!!} >`);
        $('div #{{$field->name}}').focus();
    })
</script>
@endpush
@endisset


