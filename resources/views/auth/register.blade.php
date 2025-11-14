@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8">
	<h1 class="text-2xl font-bold mb-6">Create account</h1>
	@if ($errors->any())
		<div class="bg-red-100 text-red-700 p-3 mb-4">{{ $errors->first() }}</div>
	@endif
	<form method="POST" action="{{ route('register.post') }}" class="space-y-4">
		@csrf
		<div>
			<label class="block mb-1">Name</label>
			<input name="name" type="text" value="{{ old('name') }}" class="w-full border p-2" required />
		</div>
		<div>
			<label class="block mb-1">Email</label>
			<input name="email" type="email" value="{{ old('email') }}" class="w-full border p-2" required />
		</div>
		<div>
			<label class="block mb-1">Password</label>
			<input name="password" type="password" class="w-full border p-2" required />
		</div>
		<div>
			<label class="block mb-1">Confirm Password</label>
			<input name="password_confirmation" type="password" class="w-full border p-2" required />
		</div>
		<button type="submit" class="bg-blue-600 text-white px-4 py-2">Sign up</button>
	</form>
</div>
@endsection


