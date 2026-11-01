@section('nav')
<header class="fixed top-0 start-0 z-20 w-full border-b border-default shadow-sm bg-white dark:bg-gray-900">
{{-- 2. Nav atas juga diberi bg-white/solid agar tidak tembus --}}
  <nav class="bg-white dark:bg-gray-900 border-b border-default/50">
      <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl p-4">
          <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse group">
              <img src="{{ asset('image/logo.png') }}" class="h-8 transition-transform group-hover:scale-105" alt="Logo" />
              <div class="lobster-regular">
                <span class="self-center text-xl text-heading font-bold whitespace-nowrap tracking-tight">Hanif Catering</span>
              </div>
          </a>
          
          <div class="flex items-center space-x-4 rtl:space-x-reverse">             
            <a href="login">
            <button type="button" data-modal-target="crud-modal" data-modal-toggle="crud-modal" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-md transition-all duration-200 shadow-sm hover:shadow-md focus:ring-4 focus:ring-blue-300 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#FFFFFF">
                    <path d="M234-276q51-39 114-61.5T480-360q69 0 132 22.5T726-276q35-41 54.5-93T800-480q0-133-93.5-226.5T480-800q-133 0-226.5 93.5T160-480q0 59 19.5 111t54.5 93Zm146.5-204.5Q340-521 340-580t40.5-99.5Q421-720 480-720t99.5 40.5Q620-639 620-580t-40.5 99.5Q539-440 480-440t-99.5-40.5ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm100-95.5q47-15.5 86-44.5-39-29-86-44.5T480-280q-53 0-100 15.5T294-220q39 29 86 44.5T480-160q53 0 100-15.5ZM523-537q17-17 17-43t-17-43q-17-17-43-17t-43 17q-17 17-17 43t17 43q17 17 43 17t43-17Zm-43-43Zm0 360Z"/>
                </svg>
                Login
            </button>
            </a>
          </div>
      </div>
  </nav>
</header>
@endsection
{{-- FLOATING WHATSAPP BUTTON (ICON ONLY) --}}
<div class="fixed bottom-6 right-6 z-50">
    {{-- Pulsing ring --}}
    <div class="absolute inset-0 flex items-center justify-center">
        <div class="w-16 h-16 bg-green-400 rounded-full opacity-75 animate-ping"></div>
    </div>
        <a href="https://wa.me/6289675704781?text=Halo%20Hanif%20Catering%2C%20saya%20ingin%20bertanya%20tentang%20menu%20catering" 
        target="_blank" 
        rel="noopener noreferrer"
        class="relative flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-3 py-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105">
            
            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
        </a>
</div>