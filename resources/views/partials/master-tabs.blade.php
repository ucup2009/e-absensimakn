@php($active = $active ?? 'students')
<div class="scrollbar-hide flex flex-nowrap items-center gap-1.5 overflow-x-auto rounded-xl border border-slate-200 bg-white p-1.5">
    <a href="{{ route('students.index') }}" class="tab-link {{ $active === 'students' ? 'tab-link-active' : '' }}">
        <span class="material-symbols-outlined text-[17px]">groups</span> Data Siswa
        <span class="rounded-md px-1.5 py-0.5 text-[10px] {{ $active === 'students' ? 'bg-white/20' : 'bg-slate-100 text-slate-500' }}">{{ \App\Models\Student::count() }}</span>
    </a>
    <a href="{{ route('classes.index') }}" class="tab-link {{ $active === 'classes' ? 'tab-link-active' : '' }}">
        <span class="material-symbols-outlined text-[17px]">meeting_room</span> Rombel / Kelas
        <span class="rounded-md px-1.5 py-0.5 text-[10px] {{ $active === 'classes' ? 'bg-white/20' : 'bg-slate-100 text-slate-500' }}">{{ \App\Models\SchoolClass::count() }}</span>
    </a>
    <a href="{{ route('users.index') }}" class="tab-link {{ $active === 'users' ? 'tab-link-active' : '' }}">
        <span class="material-symbols-outlined text-[17px]">badge</span> Wali Kelas &amp; Guru
        <span class="rounded-md px-1.5 py-0.5 text-[10px] {{ $active === 'users' ? 'bg-white/20' : 'bg-slate-100 text-slate-500' }}">{{ \App\Models\User::where('role', 'wali_kelas')->count() }}</span>
    </a>
</div>
