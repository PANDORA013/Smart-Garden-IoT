<div 
    x-data="{
        toasts: [],
        addToast(message, type = 'info', duration = 3000) {
            const id = Date.now();
            this.toasts.push({ id, message, type, duration });
            setTimeout(() => this.removeToast(id), duration);
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    @show-toast.window="addToast($event.detail.message, $event.detail.type, $event.detail.duration)"
    class="fixed top-4 right-4 z-50 space-y-3 max-w-md"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div 
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-x-8"
            x-transition:enter-end="opacity-100 transform translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-x-0"
            x-transition:leave-end="opacity-0 transform translate-x-8"
            @click="removeToast(toast.id)"
            :class="{
                'bg-blue-50 border-blue-200 text-blue-800': toast.type === 'info',
                'bg-green-50 border-green-200 text-green-800': toast.type === 'success',
                'bg-yellow-50 border-yellow-200 text-yellow-800': toast.type === 'warning',
                'bg-red-50 border-red-200 text-red-800': toast.type === 'error'
            }"
            class="flex items-start gap-3 p-4 rounded-lg border shadow-lg cursor-pointer hover:shadow-xl transition-all"
        >
            <div class="shrink-0 mt-0.5">
                <i 
                    :class="{
                        'fa-info-circle text-blue-500': toast.type === 'info',
                        'fa-check-circle text-green-500': toast.type === 'success',
                        'fa-exclamation-triangle text-yellow-500': toast.type === 'warning',
                        'fa-times-circle text-red-500': toast.type === 'error'
                    }"
                    class="fa-solid text-lg"
                ></i>
            </div>
            <p class="flex-1 text-sm font-medium" x-text="toast.message"></p>
            <button 
                @click.stop="removeToast(toast.id)"
                class="shrink-0 text-slate-400 hover:text-slate-600 transition-colors"
            >
                <i class="fa-solid fa-times text-sm"></i>
            </button>
        </div>
    </template>
</div>
