<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="section-title">Cadastro de filial</div>
            <h1 class="display-title mt-2">{{ $branch->exists ? 'Editar filial' : 'Nova filial' }}</h1>
        </div>
    </x-slot>

    @php($branchSettings = old('settings', $branch->settings ?? []))

    <form method="POST" action="{{ $branch->exists ? route('filiais.update', $branch) : route('filiais.store') }}" class="panel p-8 space-y-6">
        @csrf
        @if($branch->exists)
            @method('PUT')
        @endif

        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="field-label" for="name">Nome</label>
                <input class="field-input" id="name" name="name" value="{{ old('name', $branch->name) }}" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <label class="field-label" for="slug">Slug publico</label>
                <input class="field-input" id="slug" name="slug" value="{{ old('slug', $branch->slug) }}" required />
                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
            </div>

            <div>
                <label class="field-label" for="type">Tipo</label>
                <select class="field-select" id="type" name="type">
                    @foreach ($branchTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('type', $branch->type?->value) === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('type')" class="mt-2" />
            </div>

            <div>
                <label class="field-label" for="email">E-mail</label>
                <input class="field-input" id="email" type="email" name="email" value="{{ old('email', $branch->email) }}" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label class="field-label" for="phone">Telefone interno</label>
                <input class="field-input" id="phone" name="phone" value="{{ old('phone', $branch->phone) }}" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <label class="field-label" for="address">Endereco</label>
                <input class="field-input" id="address" name="address" value="{{ old('address', $branch->address) }}" />
                <x-input-error :messages="$errors->get('address')" class="mt-2" />
            </div>

            <div>
                <label class="field-label" for="monthly_fee_default">Mensalidade padrao</label>
                <input class="field-input" id="monthly_fee_default" name="monthly_fee_default" value="{{ old('monthly_fee_default', $branch->monthly_fee_default) }}" />
                <x-input-error :messages="$errors->get('monthly_fee_default')" class="mt-2" />
            </div>

            <div>
                <label class="field-label" for="settings_city">Cidade publica</label>
                <input class="field-input" id="settings_city" name="settings[city]" value="{{ $branchSettings['city'] ?? '' }}" />
                <x-input-error :messages="$errors->get('settings.city')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <label class="field-label" for="settings_summary">Resumo publico</label>
                <textarea class="field-textarea" id="settings_summary" name="settings[summary]" maxlength="300">{{ $branchSettings['summary'] ?? '' }}</textarea>
                <x-input-error :messages="$errors->get('settings.summary')" class="mt-2" />
            </div>

            <div>
                <label class="field-label" for="settings_public_phone">Telefone publico</label>
                <input class="field-input" id="settings_public_phone" name="settings[public_phone]" value="{{ $branchSettings['public_phone'] ?? '' }}" />
                <x-input-error :messages="$errors->get('settings.public_phone')" class="mt-2" />
            </div>

            <div>
                <label class="field-label" for="settings_public_whatsapp">WhatsApp publico</label>
                <input class="field-input" id="settings_public_whatsapp" name="settings[public_whatsapp]" value="{{ $branchSettings['public_whatsapp'] ?? '' }}" />
                <x-input-error :messages="$errors->get('settings.public_whatsapp')" class="mt-2" />
            </div>

            <div class="md:col-span-2">
                <label class="field-label" for="settings_public_hours">Horario publico</label>
                <input class="field-input" id="settings_public_hours" name="settings[public_hours]" value="{{ $branchSettings['public_hours'] ?? '' }}" />
                <x-input-error :messages="$errors->get('settings.public_hours')" class="mt-2" />
            </div>

            <div class="flex items-center gap-3 pt-8">
                <input id="is_active" type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-teal-700 focus:ring-teal-500" @checked(old('is_active', $branch->is_active ?? true))>
                <label for="is_active" class="text-sm font-medium text-slate-700">Filial ativa</label>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('filiais.index') }}" class="btn-secondary">Cancelar</a>
            <button class="btn-primary" type="submit">Salvar filial</button>
        </div>
    </form>
</x-app-layout>
