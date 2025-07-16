<div id="notification" class="fixed inset-0 flex items-center justify-center z-50" style="display: {{ $show ? 'flex' : 'none' }};">
    <div class="bg-white p-6 rounded shadow-lg text-center">
        <p class="mb-4" style="color: {{ $textColor }};">{{ $message }}</p>
        <button id="accept-button" class="bg-blue-500 text-white px-4 py-2 rounded">
            Aceptar
        </button>
    </div>
</div>

<style>
    #notification {
        transition: opacity 0.5s ease;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const acceptButton = document.getElementById('accept-button');
        const notification = document.getElementById('notification');

        acceptButton.addEventListener('click', function () {
            notification.style.display = 'none';
        });
    });
</script>