<section>
    <header class="mb-5">
        <h2 class="text-lg font-semibold text-error">Eliminar cuenta</h2>
        <p class="text-sm text-base-content/60">
            Su cuenta será dada de baja lógica. Contacte al administrador si necesita recuperar el acceso.
        </p>
    </header>

    <button type="button" class="btn btn-error btn-outline"
            onclick="document.getElementById('delete-account-modal').showModal()">
        Eliminar mi cuenta
    </button>

    <dialog id="delete-account-modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold">¿Eliminar su cuenta?</h3>
            <p class="py-4 text-sm text-base-content/70">Ingrese su contraseña para confirmar esta acción.</p>
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <x-ui.field label="Contraseña" for="delete_password"
                            :error="$errors->userDeletion->first('password')">
                    <x-ui.input id="delete_password" name="password" type="password" required
                                autocomplete="current-password" />
                </x-ui.field>

                <div class="modal-action">
                    <button type="button" class="btn btn-ghost"
                            onclick="document.getElementById('delete-account-modal').close()">Cancelar</button>
                    <button type="submit" class="btn btn-error">Confirmar eliminación</button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop"><button type="submit">Cerrar</button></form>
    </dialog>
</section>

@if ($errors->userDeletion->isNotEmpty())
    <script>document.getElementById('delete-account-modal')?.showModal();</script>
@endif
