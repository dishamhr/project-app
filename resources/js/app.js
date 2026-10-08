// Task Reminder Mahasiswa - frontend behaviour

const INDONESIAN_DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
const INDONESIAN_MONTHS = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

function formatTanggalIndonesia(date) {
    return `${INDONESIAN_DAYS[date.getDay()]}, ${date.getDate()} ${INDONESIAN_MONTHS[date.getMonth()]} ${date.getFullYear()}, ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

/* ------------------------------------------------------------------ */
/* Navigasi sidebar (mobile)                                          */
/* ------------------------------------------------------------------ */

const sidebar = document.getElementById('sidebar');
const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');

function openSidebar() {
    if (!sidebar) return;
    sidebar.classList.remove('-translate-x-full');
    sidebarOverlay?.classList.remove('hidden');
}

function closeSidebar() {
    if (!sidebar) return;
    sidebar.classList.add('-translate-x-full');
    sidebarOverlay?.classList.add('hidden');
}

document.querySelector('[data-sidebar-open]')?.addEventListener('click', openSidebar);
document.querySelector('[data-sidebar-close]')?.addEventListener('click', closeSidebar);
sidebarOverlay?.addEventListener('click', closeSidebar);

/* ------------------------------------------------------------------ */
/* Panel lonceng                                                      */
/* ------------------------------------------------------------------ */

const bellToggle = document.querySelector('[data-bell-toggle]');
const bellPanel = document.querySelector('[data-bell-panel]');
const bellBadge = document.querySelector('[data-bell-badge]');
const bellList = document.querySelector('[data-bell-list]');

function closeBellPanel() {
    if (bellPanel && !bellPanel.hasAttribute('hidden')) {
        bellPanel.setAttribute('hidden', '');
    }
}

bellToggle?.addEventListener('click', (event) => {
    event.stopPropagation();
    if (!bellPanel) return;
    if (bellPanel.hasAttribute('hidden')) {
        bellPanel.removeAttribute('hidden');
    } else {
        bellPanel.setAttribute('hidden', '');
    }
});

document.addEventListener('click', (event) => {
    if (bellPanel && !bellPanel.hasAttribute('hidden') && !bellPanel.contains(event.target)) {
        closeBellPanel();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeBellPanel();
        closeSidebar();
    }
});

/* ------------------------------------------------------------------ */
/* Konfirmasi hapus & alert                                            */
/* ------------------------------------------------------------------ */

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('[data-dismiss]').forEach((button) => {
    button.addEventListener('click', () => {
        const alert = button.closest('div');
        if (alert) alert.remove();
    });
});

/* ------------------------------------------------------------------ */
/* Suara notifikasi                                                   */
/* ------------------------------------------------------------------ */

let audioContext = null;

function ensureAudio() {
    const AudioCtx = window.AudioContext || window.webkitAudioContext;
    if (!AudioCtx) return null;

    if (!audioContext) audioContext = new AudioCtx();
    if (audioContext.state === 'suspended') audioContext.resume();

    return audioContext;
}

['pointerdown', 'keydown'].forEach((eventName) => {
    document.addEventListener(eventName, () => ensureAudio(), { once: true });
});

function playNotificationSound() {
    try {
        const ctx = ensureAudio();
        if (!ctx) return;

        const now = ctx.currentTime;
        const gain = ctx.createGain();
        gain.gain.setValueAtTime(0.0001, now);
        gain.gain.exponentialRampToValueAtTime(0.25, now + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + 0.9);
        gain.connect(ctx.destination);

        [880, 660, 990].forEach((frequency, index) => {
            const oscillator = ctx.createOscillator();
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(frequency, now + index * 0.18);
            oscillator.connect(gain);
            oscillator.start(now + index * 0.18);
            oscillator.stop(now + index * 0.18 + 0.35);
        });
    } catch (error) {
        // Abaikan jika browser memblokir audio.
    }
}

/* ------------------------------------------------------------------ */
/* Toast pengingat                                                    */
/* ------------------------------------------------------------------ */

const toastContainer = document.querySelector('[data-toast-container]');

function showToast(item, soundEnabled) {
    if (!toastContainer) return;

    const toast = document.createElement('div');
    toast.className = 'pointer-events-auto overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200';
    toast.innerHTML = `
        <div class="flex items-start gap-3 bg-brand-600 px-4 py-3 text-white">
            <span class="text-lg leading-none">🔔</span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold">${escapeHtml(item.title)}</p>
                <p class="text-xs text-blue-100">Baru saja</p>
            </div>
            <button type="button" data-toast-close class="rounded-lg p-1 hover:bg-white/20" aria-label="Tutup">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-4 py-3">
            <p class="whitespace-pre-line text-sm leading-relaxed text-slate-700">${escapeHtml(item.message)}</p>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
            <button type="button" data-toast-close class="btn btn-secondary btn-sm">Tutup</button>
            <a href="${escapeHtml(item.url)}" data-toast-read class="btn btn-primary btn-sm">Lihat Tugas</a>
        </div>`;

    toastContainer.appendChild(toast);
    if (soundEnabled) playNotificationSound();

    const remove = () => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(8px)';
        toast.style.transition = 'opacity .2s ease, transform .2s ease';
        setTimeout(() => toast.remove(), 220);
    };

    toast.querySelectorAll('[data-toast-close]').forEach((button) => {
        button.addEventListener('click', remove);
    });

    toast.querySelector('[data-toast-read]')?.addEventListener('click', () => {
        markAsRead(item.id);
    });

    setTimeout(() => {
        if (toast.isConnected) remove();
    }, 15000);
}

/* ------------------------------------------------------------------ */
/* API pengingat                                                      */
/* ------------------------------------------------------------------ */

const config = window.AppConfig;
const seenReminderIds = new Set();

function csrfOptions(method = 'GET') {
    return {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            'Content-Type': 'application/json',
        },
    };
}

async function markAsRead(id) {
    try {
        await fetch(`/reminders/${id}/read`, csrfOptions('PATCH'));
        seenReminderIds.add(id);
        await loadReminders(true);
    } catch (error) {
        // abaikan
    }
}

function updateBadge(count) {
    if (!bellBadge) return;
    if (count > 0) {
        bellBadge.textContent = count > 99 ? '99+' : String(count);
        bellBadge.classList.remove('hidden');
    } else {
        bellBadge.classList.add('hidden');
    }
}

function renderBellList(items) {
    if (!bellList) return;

    if (!items.length) {
        bellList.innerHTML = '<p class="px-4 py-6 text-center text-sm text-slate-500">Tidak ada pengingat baru.</p>';
        return;
    }

    bellList.innerHTML = items.map((item) => `
        <a href="${escapeHtml(item.url)}" data-bell-item="${item.id}" class="block px-4 py-3 transition hover:bg-slate-50">
            <p class="text-sm font-semibold text-slate-900">${escapeHtml(item.title)}</p>
            <p class="mt-0.5 line-clamp-2 whitespace-pre-line text-xs text-slate-500">${escapeHtml(item.message)}</p>
        </a>`).join('');

    bellList.querySelectorAll('[data-bell-item]').forEach((link) => {
        link.addEventListener('click', () => {
            markAsRead(link.dataset.bellItem);
        });
    });
}

async function loadReminders(initial = false) {
    if (!config?.remindersUrl) return;

    try {
        const response = await fetch(config.remindersUrl, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        if (!response.ok) return;

        const data = await response.json();

        updateBadge(data.count ?? 0);
        renderBellList(data.items ?? []);

        const freshItems = (data.items ?? []).filter((item) => !seenReminderIds.has(item.id));
        freshItems.forEach((item) => seenReminderIds.add(item.id));

        if (!initial || freshItems.length > 0) {
            freshItems.forEach((item) => showToast(item, Boolean(data.sound)));
        }
    } catch (error) {
        // Jaringan bermasalah -> coba lagi di siklus berikutnya.
    }
}

document.querySelector('[data-mark-all-read]')?.addEventListener('click', async () => {
    if (!config?.markAllReadUrl) return;
    try {
        await fetch(config.markAllReadUrl, csrfOptions('PATCH'));
        updateBadge(0);
        renderBellList([]);
    } catch (error) {
        // abaikan
    }
});

if (config?.remindersUrl) {
    loadReminders(true);
    setInterval(() => loadReminders(false), 20000);
}

/* ------------------------------------------------------------------ */
/* Pratinjau waktu pengingat pada form tugas                           */
/* ------------------------------------------------------------------ */

const dateInput = document.getElementById('deadline_date');
const timeInput = document.getElementById('deadline_time');
const reminderSelect = document.getElementById('reminder_minutes');
const reminderPreview = document.querySelector('[data-reminder-preview]');

if (dateInput && timeInput && reminderSelect && reminderPreview) {
    const updatePreview = () => {
        if (!dateInput.value || !timeInput.value) return;

        const deadline = new Date(`${dateInput.value}T${timeInput.value}`);
        if (Number.isNaN(deadline.getTime())) return;

        const offset = Number.parseInt(reminderSelect.value, 10) || 0;
        deadline.setMinutes(deadline.getMinutes() - offset);

        reminderPreview.textContent = formatTanggalIndonesia(deadline);
    };

    [dateInput, timeInput, reminderSelect].forEach((element) => {
        element.addEventListener('change', updatePreview);
    });
}
