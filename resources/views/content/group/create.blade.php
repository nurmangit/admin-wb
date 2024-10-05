@extends('layouts/contentNavbarLayout')

@section('title', 'Group - Forms')

@section('content')
<h4 class="py-3 mb-4"><span class="text-muted fw-light">Forms /</span> Group / Create</h4>

<div class="row">
  <div class="col-xl">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Group Details</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('account.group.store') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="form-label" for="name">Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Name" required value="{{ old('name') }}" />
            @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
          </div>

          <label class="form-label" for="permission">Permissions</label>
          <div class="d-flex flex-row flex-wrap">
            @foreach($permissions as $permission)
            <div class="mb-3 col-2 p-2">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" id="{{$permission}}" name="{{$permission}}" {{ old('$permission') ? 'checked' : '' }}>
                <label class="form-check-label" for="{{$permission}}">{{$permission}}</label>
              </div>
            </div>
            @endforeach
          </div>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection