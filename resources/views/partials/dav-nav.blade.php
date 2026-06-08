<nav class="nav">
    <a href="{{ route('contacts.index') }}" @class(['is-active' => ($active ?? '') === 'contacts'])>Contactos</a>
    <a href="{{ route('calendar.index') }}" @class(['is-active' => ($active ?? '') === 'calendar'])>Calendario</a>
    <a href="{{ route('tasks.index') }}" @class(['is-active' => ($active ?? '') === 'tasks'])>Tareas</a>
    <form method="post" action="{{ route('logout') }}" class="nav-logout">
        @csrf
        <button type="submit">Salir</button>
    </form>
</nav>
