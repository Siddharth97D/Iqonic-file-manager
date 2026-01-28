<div 
    x-data="{
        visible: false,
        top: 0,
        left: 0,
        file: null,
        
        init() {
            window.addEventListener('fm:context-menu', (e) => {
                this.show(e.detail.event, e.detail.file);
            });
            window.addEventListener('click', () => {
                this.hide();
            });
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') this.hide();
            });
        },
        
        show(event, file) {
            event.preventDefault();
            this.file = file;
            this.visible = true;
            this.left = event.clientX;
            this.top = event.clientY;
            
            // Adjust if out of bounds (simple usage)
            this.$nextTick(() => {
                const el = this.$el;
                if (this.left + el.offsetWidth > window.innerWidth) {
                    this.left = window.innerWidth - el.offsetWidth - 10;
                }
                if (this.top + el.offsetHeight > window.innerHeight) {
                    this.top = window.innerHeight - el.offsetHeight - 10;
                }
            });
        },
        
        hide() {
            this.visible = false;
            this.file = null;
        },

        action(type) {
            if (!this.file) return;
            
            switch(type) {
                case 'preview':
                    window.dispatchEvent(new CustomEvent('fm:preview', { detail: { file: this.file } }));
                    break;
                case 'share':
                    window.dispatchEvent(new CustomEvent('fm:share', { detail: { file: this.file } }));
                    break;
                case 'rename':
                    // Trigger rename logic (needs implementation in layout JS/Alpine)
                    // Assuming we can emit an event similar to preview
                    window.dispatchEvent(new CustomEvent('fm:rename-prompt', { detail: { file: this.file } }));
                    break;
                case 'download':
                     window.location.href = `${window.apiBaseUrl}/files/${this.file.id}/download`;
                    break;
                case 'delete':
                     window.dispatchEvent(new CustomEvent('fm:delete-confirm', { detail: { files: [this.file] } }));
                    break;
            }
            this.hide();
        }
    }"
    x-show="visible" 
    :style="`top: ${top}px; left: ${left}px`"
    class="fixed z-50 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-1 text-sm text-gray-700 dark:text-gray-200"
    style="display: none;"
    x-cloak
    @contextmenu.prevent
>
    <!-- Preview -->
    <a href="#" @click.prevent="action('preview')" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center">
        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
        Preview
    </a>

    <!-- Rename -->
    <a href="#" @click.prevent="action('rename')" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center">
        <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
        Rename
    </a>

    <!-- Share -->
    <a href="#" @click.prevent="action('share')" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center">
        <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
        Share
    </a>

    <!-- Download -->
    <a href="#" @click.prevent="action('download')" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center">
        <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
        Download
    </a>
    
    <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>

    <!-- Delete -->
    <a href="#" @click.prevent="action('delete')" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 text-red-600 dark:text-red-400 flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        Delete
    </a>
</div>
