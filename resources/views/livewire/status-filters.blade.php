<nav class="hidden md:flex items-center justify-between text-xs">
    <ul class="flex uppercase font-semibold border-b-4 pb-3 space-x-10">
        <li>
            <a 
                wire:click.prevent="setStatus('')"
                href="{{ route('idea.index', ['status' => '']) }}" 
                class="border-b-4 pb-3 hover:border-blue @if($status === '')) text-gray-900 border-blue @endif"
            >
                All Ideas ({{ $statusCount['all_statuses'] }})
            </a>
        </li>
        <li>
            <a 
                wire:click.prevent="setStatus('Considering')"
                href="{{ route('idea.index', ['status' => 'Considering']) }}" 
                class="text-gray-400 transition duration-150 ease-in border-b-4 pb-3 hover:border-blue @if($status === 'Considering') text-gray-900 border-blue @endif"
            >
                Considering ({{ $statusCount['considering'] }})
            </a>
        </li>
        <li>
            <a 
                wire:click.prevent="setStatus('In Progress')"
                href="{{ route('idea.index', ['status' => 'In Progress']) }}" 
                class="text-gray-400 transition duration-150 ease-in border-b-4 pb-3 hover:border-blue @if($status === 'In Progress') text-gray-900 border-blue @endif"
            >
                In Progress ({{ $statusCount['in_progress'] }})
            </a>
        </li>
    </ul>
    <ul class="flex uppercase font-semibold border-b-4 pb-3 space-x-10">
        <li>
            <a 
                wire:click.prevent="setStatus('Implemented')"
                href="{{ route('idea.index', ['status' => 'Implemented']) }}" 
                class="text-gray-400 transition duration-150 ease-in border-b-4 pb-3 hover:border-blue @if($status === 'Implemented') text-gray-900 border-blue @endif"
            >
                Implemented ({{ $statusCount['implemented'] }})
            </a>
        </li>
        <li>
            <a 
                wire:click.prevent="setStatus('Closed')"
                href="{{ route('idea.index', ['status' => 'Closed']) }}" 
                class="text-gray-400 transition duration-150 ease-in border-b-4 pb-3 hover:border-blue @if($status === 'Closed') text-gray-900 border-blue @endif"
            >
                Closed ({{ $statusCount['closed'] }})
            </a>
        </li>
    </ul>
</nav>
