<script>
  import { Link } from 'svelte-routing';
  import { onMount } from 'svelte';

  let scrolled = false;
  let openFaq = null;
  let showScrollTop = false;
  let mobileMenuOpen = false;
  let showFloatingWhatsApp = false;

  onMount(() => {
    const handleScroll = () => {
      scrolled = window.scrollY > 50;
      showScrollTop = window.scrollY > 300; // Mostrar después de 300px de scroll
      showFloatingWhatsApp = window.scrollY > window.innerHeight * 0.8; // Mostrar después de 80% de la altura de la pantalla (pasada la sección hero)
    };

    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  });

  function toggleFaq(index) {
    openFaq = openFaq === index ? null : index;
  }

  function smoothScrollToSection(event, sectionId) {
    event.preventDefault();
    const element = document.getElementById(sectionId);
    if (element) {
      element.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  }

  function scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }

  function toggleMobileMenu() {
    mobileMenuOpen = !mobileMenuOpen;
  }

  const faqs = [
    {
      question: "¿Tengo que estar en mi casa cuando vienen?",
      answer: "No, trabajamos de manera autónoma."
    },
    {
      question: "¿Puedo cancelar cuando quiera?",
      answer: "Sí, sin contratos."
    },
    {
      question: "¿Qué pasa si llueve?",
      answer: "Reprogramamos fecha."
    },
    {
      question: "¿En qué día del mes vienen?",
      answer: "Te asignamos una fecha fija según tu zona y plan."
    },
    {
      question: "¿Qué incluye exactamente la visita?",
      answer: "Corte, bordes, repaso en zonas de uso y un monitoreo del jardín."
    },
    {
      question: "¿Qué incluye el monitoreo?",
      answer: `Cada suscriptor tiene un acceso personal donde puede ver el estado y la evolución de su jardín cuando quiera de manera online.

El panel incluye:
• Estado general del césped: Indicador general del jardín (Bueno / Regular / A mejorar) con detalle sobre parejidad, color, manchas, zonas desgastadas y presencia de malezas, más una nota explicativa.
• Evolución del jardín en el tiempo: Comparación visual entre el mes actual y meses anteriores mediante imágenes cenitales (con drone).
• Nivel de crecimiento del mes: Medición aproximada del crecimiento del césped en centímetros, con categoría general (Bajo / Normal / Alto)
• Estado de compactación del suelo: Observación general del suelo (suelto o compacto) y recomendación si conviene aireado.
• Estado de humedad y riego: Evaluación visual del riego (seco, correcto o exceso de agua) con sugerencias simples de ajuste.
• Presencia de plagas o enfermedades visibles: Aviso si se detectan señales leves o situaciones a observar.
• Estado de canteros y bordes: Indicación de canteros prolijos o con malezas visibles y si requieren mantenimiento.
• Recomendaciones estacionales: Sugerencias prácticas sobre riego, corte, siembra o resiembra, fertilización y qué evitar ese mes.
• Historial de visitas: Registro de todas las visitas realizadas, con fecha y estado general en cada una.

Acceso al monitoreo: El panel está disponible las 24 horas y podés ingresar con tu usuario y clave, cuando quieras para consultar el estado actual de tu jardín o ver su evolución mes a mes.

Aclaración: El monitoreo es informativo y forma parte de la suscripción. Los trabajos adicionales o intervenciones específicas se proponen como servicios complementarios y no están incluidos en el corte mensual.`
    },
    {
      question: "¿Puedo pedir un corte extra o un tema puntual?",
      answer: "Sí, con 30% de descuento para suscriptores."
    },
    {
      question: "No sé cuántos m² tiene mi jardín, ¿qué hago?",
      answer: "Escribinos y te ayudamos a definir cuál es el mejor plan."
    },
    {
      question: "Mi casa tiene muy pocos m2, ¿no hay plan más chico?",
      answer: "El Plan Urbano está pensado para jardines chicos. Si tu caso es especial, escribinos y lo evaluamos."
    },
    {
      question: "¿Cuáles son los extras que hacen en CESPED365?",
      answer: `1. Recolección y retiro de pasto (Embolsado y retiro del césped cortado)
2. Cortes extras mensuales (Cortes adicionales fuera del incluido en la suscripción con descuento)
3. Resiembra o siembra de césped (Reparación de zonas peladas o dañadas)
4. Aireado del césped (Perforación del suelo para mejorar oxigenación y drenaje)
5. Nivelación simple del terreno (Corrección de pequeños pozos o desniveles)
6. Fertilización básica del césped (Aplicación de fertilizante estacional)
7. Desmalezado de canteros (Eliminación de malezas en zonas plantadas)
8. Reposición de tierra, corteza o chips
9. Limpieza de hojas (Recolección de hojas caídas)
10. Corte de arbustos bajos (Mantenimiento liviano sin poda grande)`
    }
  ];
</script>

<div class="min-h-screen bg-white">
  <!-- Header/Navbar -->
  <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 {scrolled ? 'bg-gray-900 shadow-lg' : 'bg-transparent'}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-20">
        <!-- Logo -->
        <div class="flex items-center">
          <div class="flex items-center gap-2">
            <img src="/logo.png" alt="Logo" class="logo">
          </div>
        </div>
        
        <!-- Desktop Navigation Menu -->
        <div class="hidden md:flex items-center space-x-8">
          <a href="#home" on:click={(e) => smoothScrollToSection(e, 'home')} class="text-white hover:text-green-400 font-medium transition-colors cursor-pointer">Home</a>
          <a href="#beneficios" on:click={(e) => smoothScrollToSection(e, 'beneficios')} class="text-white hover:text-green-400 font-medium transition-colors cursor-pointer">Beneficios</a>
          <a href="#categorias" on:click={(e) => smoothScrollToSection(e, 'categorias')} class="text-white hover:text-green-400 font-medium transition-colors cursor-pointer">Planes</a>
          <a href="#como-funciona" on:click={(e) => smoothScrollToSection(e, 'como-funciona')} class="text-white hover:text-green-400 font-medium transition-colors cursor-pointer">Como funciona</a>
          <a href="#faq" on:click={(e) => smoothScrollToSection(e, 'faq')} class="text-white hover:text-green-400 font-medium transition-colors cursor-pointer">FAQ</a>
          <a href="#about" on:click={(e) => smoothScrollToSection(e, 'about')} class="text-white hover:text-green-400 font-medium transition-colors cursor-pointer">Quienes somos</a>
        </div>

        <!-- Desktop CTA Buttons -->
        <div class="hidden md:flex items-center">
          <!-- Botones de login ocultos temporalmente
          <Link to="/login" class="mr-4 text-white px-6 py-3 rounded-full hover:opacity-90 font-medium transition-all shadow-lg flex items-center gap-3 group" style="background-image: linear-gradient(to right, #000000, #262c29);">
            Registrarse
          </Link>

          <Link to="/login" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3
          rounded-full hover:from-green-600 hover:to-green-700 font-medium transition-all shadow-lg flex items-center gap-3">
            Iniciar sesión
          </Link>
          -->
        </div>

        <!-- Mobile Menu Button -->
        <div class="md:hidden">
          <button
            on:click={toggleMobileMenu}
            class="p-2 text-white hover:text-green-400 focus:outline-none focus:text-green-400 transition-colors"
            aria-label="Toggle mobile menu"
          >
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              {#if mobileMenuOpen}
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              {:else}
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              {/if}
            </svg>
          </button>
        </div>
      </div>

    </div>
  </nav>

  <!-- Mobile Menu Overlay -->
  {#if mobileMenuOpen}
    <div 
      class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden transition-opacity duration-300"
      on:click={() => mobileMenuOpen = false}
      on:keydown={(e) => e.key === 'Escape' && (mobileMenuOpen = false)}
      role="button"
      tabindex="-1"
      aria-label="Close menu overlay"
    ></div>
  {/if}

  <!-- Mobile Menu Sidebar -->
  <div 
    class="fixed top-0 right-0 h-full w-80 bg-gray-900 shadow-2xl z-50 md:hidden transform transition-transform duration-300 ease-in-out {mobileMenuOpen ? 'translate-x-0' : 'translate-x-full'}"
  >
    <!-- Close Button -->
    <div class="flex justify-end p-4">
      <button
        on:click={() => mobileMenuOpen = false}
        class="text-white hover:text-green-400 focus:outline-none"
        aria-label="Close menu"
      >
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

      <!-- Menu Content -->
    <div class="px-6 py-4 space-y-6 overflow-y-auto h-full pb-20">
      <!-- Navigation Links -->
      <nav class="space-y-4">
        <a
          href="#home"
          on:click={(e) => {smoothScrollToSection(e, 'home'); mobileMenuOpen = false;}}
          class="block px-4 py-3 text-white hover:text-green-400 hover:bg-gray-800 rounded-lg font-medium transition-all cursor-pointer"
        >
          Home
        </a>
        <a
          href="#beneficios"
          on:click={(e) => {smoothScrollToSection(e, 'beneficios'); mobileMenuOpen = false;}}
          class="block px-4 py-3 text-white hover:text-green-400 hover:bg-gray-800 rounded-lg font-medium transition-all cursor-pointer"
        >
          Beneficios
        </a>
        <a
          href="#categorias"
          on:click={(e) => {smoothScrollToSection(e, 'categorias'); mobileMenuOpen = false;}}
          class="block px-4 py-3 text-white hover:text-green-400 hover:bg-gray-800 rounded-lg font-medium transition-all cursor-pointer"
        >
          Planes
        </a>
        <a
          href="#como-funciona"
          on:click={(e) => {smoothScrollToSection(e, 'como-funciona'); mobileMenuOpen = false;}}
          class="block px-4 py-3 text-white hover:text-green-400 hover:bg-gray-800 rounded-lg font-medium transition-all cursor-pointer"
        >
          Como funciona
        </a>
        <a
          href="#faq"
          on:click={(e) => {smoothScrollToSection(e, 'faq'); mobileMenuOpen = false;}}
          class="block px-4 py-3 text-white hover:text-green-400 hover:bg-gray-800 rounded-lg font-medium transition-all cursor-pointer"
        >
          FAQ
        </a>
        <a
          href="#about"
          on:click={(e) => {smoothScrollToSection(e, 'about'); mobileMenuOpen = false;}}
          class="block px-4 py-3 text-white hover:text-green-400 hover:bg-gray-800 rounded-lg font-medium transition-all cursor-pointer"
        >
          Quienes somos
        </a>
      </nav>

      <!-- Divider -->
      <div class="border-t border-gray-700 my-6"></div>

      <!-- CTA Buttons -->
      <div class="space-y-3">
        <!-- Botones de login ocultos temporalmente
        <Link
          to="/login"
          on:click={() => mobileMenuOpen = false}
          class="block w-full text-center text-white px-6 py-4 rounded-full hover:opacity-90 font-semibold text-lg transition-all shadow-lg"
          style="background-image: linear-gradient(to right, #000000, #262c29);"
        >
          Registrarse
        </Link>

        <Link
          to="/login"
          on:click={() => mobileMenuOpen = false}
          class="block w-full text-center bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-4 rounded-full hover:from-green-600 hover:to-green-700 font-semibold text-lg transition-all shadow-lg"
        >
          Iniciar sesión
        </Link>
        -->
      </div>
    </div>
  </div>

  <!-- Hero Section -->
  <section id="home" class="relative min-h-screen flex items-center">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0 z-0">
      <img 
        src="/hero.webp" 
        alt="Beautiful garden" 
        class="w-full h-full object-cover"
      />
      <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-gray-900/70 to-transparent"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 w-full">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32">
        <div class="max-w-3xl">
          <!-- Small heading -->
      
          
          <!-- Main heading -->
          <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-tight mb-8">
            Un problema menos <br> tods los meses <br>
            <span class="subheading"> Mantenimiento de jardines <br> durante todo el año.</span>

            <span class="text-[#f8fd6c] tandil inline-flex items-center">
              <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Tandil y alrededores
            </span>
          </h1>

          <!-- CTA Button -->
          <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-0 mt-[50px]">
            <a href="#categorias" on:click={(e) => smoothScrollToSection(e, 'categorias')} class="w-full sm:w-auto sm:mr-4 text-white px-8 py-4 rounded-full hover:bg-gray-800 font-semibold text-lg transition-all shadow-xl flex items-center justify-center gap-3 group bg-black">
              Elegir mi categoría

              <div class="w-10 h-10 rounded-full bg-[#58bf53] flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
              </div>
            </a>

            <a
            href="https://wa.me/5491170985242"
            target="_blank"
            rel="noopener noreferrer" class="w-full sm:w-auto bg-gradient-to-r from-green-500 to-green-600 text-white px-8 py-4 rounded-full hover:opacity-90 font-semibold text-lg transition-all shadow-xl flex items-center justify-center gap-3 group">
              Hablar por WhatsApp

              <div class="w-10 h-10 rounded-full bg-[#58bf53] flex items-center justify-center group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>



<!-- Beneficios Exclusivos del Suscriptor -->
<section class="py-20" id="beneficios">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid lg:grid-cols-2 gap-16 items-center">
      <!-- Left side - Content -->
      <div>
        <h2 class="text-2xl md:text-[48px] md:leading-[50px] font-bold text-gray-900 mb-6">
          Beneficios exclusivos del <span class="text-green-600">suscriptor</span>
        </h2>
        

        <!-- Benefits list -->
        <div class="space-y-4">
          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-600 flex items-center justify-center mt-1">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <p class="text-gray-700 font-medium lg:text-xl">Visitas programadas y cumplimiento real</p>
          </div>

          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-600 flex items-center justify-center mt-1">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <p class="text-gray-700 font-medium lg:text-xl">Un corte de césped mensual incluido</p>
          </div>

          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-600 flex items-center justify-center mt-1">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <p class="text-gray-700 font-medium lg:text-xl">Jardín prolijo y parejo todo el año incluido bordes</p>
          </div>

          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-600 flex items-center justify-center mt-1">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <p class="text-gray-700 font-medium lg:text-xl">Monitoreo del jardín: seguimiento mensual, fotos y recomendaciones estacionales</p>
          </div>

          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-600 flex items-center justify-center mt-1">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <p class="text-gray-700 font-medium lg:text-xl">Pago automático, sin llamadas ni coordinaciones</p>
          </div>

          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-600 flex items-center justify-center mt-1">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <p class="text-gray-700 font-medium lg:text-xl">Precio mensual congelado todo el año</p>
          </div>

          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-600 flex items-center justify-center mt-1">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <p class="text-gray-700 font-medium lg:text-xl">30% de descuento en cortes adicionales y servicios complementarios</p>
          </div>

          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-600 flex items-center justify-center mt-1">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <p class="text-gray-700 font-medium lg:text-xl">Menos molestias y cero preocupaciones por el mantenimiento del césped</p>
          </div>

          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-green-600 flex items-center justify-center mt-1">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <p class="text-gray-700 font-medium lg:text-xl">Podés cancelar la suscripción cuando quieras, sin contratos</p>
          </div>
        </div>
      </div>

      <!-- Right side - Images -->
      <div class="relative">


        <!-- Top small image -->
        <div class="relative overflow-hidden ml-8 mb-8 ">
          <img
            src="/quienes.webp"
            alt="Jardinera trabajando"
            class="w-full h-full object-cover"
          />
        </div>


      </div>
  </div>
</section>




  <!-- Qué incluye el servicio por suscripción -->
  <section class="py-20 bg-gradient-to-br from-green-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="text-center mb-16">
        <h2 class="text-2xl md:text-5xl font-bold text-gray-900 mb-4">
          ¿Qué incluye el servicio por <span class="text-green-600">suscripción</span>?
        </h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          Todo lo que necesitás para mantener tu jardín impecable, sin complicaciones
        </p>
      </div>

      <!-- Features Grid -->
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Feature 1 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border-2 border-green-100">
          <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">Corte programado mensual</h3>
          <p class="text-gray-600 leading-relaxed">
            Un corte programado por mes + bordes + repaso de zonas de uso. Sin coordinar, sin llamadas.
          </p>
        </div>

        <!-- Feature 2 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border-2 border-green-100">
          <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">Pago automático</h3>
          <p class="text-gray-600 leading-relaxed">
            Débito automático mensual. Olvidate de estar pendiente de pagos y facturas.
          </p>
        </div>

        <!-- Feature 3 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border-2 border-green-100">
          <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">Precio congelado</h3>
          <p class="text-gray-600 leading-relaxed">
            Tu precio mensual se mantiene fijo todo el año. Sin sorpresas ni aumentos.
          </p>
        </div>

        <!-- Feature 4 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border-2 border-green-100">
          <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">30% de descuento exclusivo</h3>
          <p class="text-gray-600 leading-relaxed">
            Descuento del 30% en cortes extras y servicios complementarios solo para suscriptores.
          </p>
        </div>

        <!-- Feature 5 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border-2 border-green-100">
          <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">Monitoreo del jardín</h3>
          <p class="text-gray-600 leading-relaxed">
            Seguimiento mensual con fotos, evolución del césped y recomendaciones estacionales personalizadas.
          </p>
        </div>

        <!-- Feature 6 -->
        <div class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-shadow duration-300 border-2 border-green-100">
          <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-3">Visitas garantizadas</h3>
          <p class="text-gray-600 leading-relaxed">
            Visitas programadas con cumplimiento real. Sabés exactamente cuándo vamos sin tener que coordinar.
          </p>
        </div>
      </div>

      
    </div>
  </section>

  

  

  <!-- Elegí la categoría de tu jardín -->
  <section class="py-20" id="categorias">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="text-center mb-12">
        <div class="flex items-center justify-center gap-2 mb-4">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          <span class="text-green-600 font-semibold text-sm uppercase tracking-wider">Plan de Precios</span>
        </div>
        <h2 class="text-2xl md:text-5xl font-bold text-gray-900 mb-4">Elegí la categoría de tu jardín</h2>
      </div>

      <!-- Pricing Cards -->
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Urbano -->
        <div class="bg-white border-green-100 border-2 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 flex flex-col">
          <h3 class="text-xl font-bold text-gray-900 mb-4">Urbano</h3>
          <div class="mb-6">
            <span class="text-3xl font-bold text-green-600">$45.000</span>
            <span class="text-gray-600">/mes</span>
          </div>
          <p class="text-gray-600 mb-6 text-xl">Hasta 500 m² de tu jardín</p>
          <div class="mt-auto">
            <a 
            href="https://wa.me/5491170985242" 
            target="_blank" 
            rel="noopener noreferrer"
            class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-full font-semibold transition-all shadow-lg flex items-center justify-center gap-2"
          >
           Consultar
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
          </a>
          </div>
        </div>

        <!-- Card 2: Residencial -->
        <div class="bg-white border-green-100 border-2 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 flex flex-col">
          <h3 class="text-xl font-bold text-gray-900 mb-4">Residencial</h3>
          <div class="mb-6">
            <span class="text-3xl font-bold text-green-600">$60.000</span>
            <span class="text-gray-600">/mes</span>
          </div>
          <p class="text-gray-600 mb-6 text-xl">500 a 2.500 m²</p>
          <div class="mt-auto">
            <a
            href="https://wa.me/5491170985242"
            target="_blank"
            rel="noopener noreferrer"
            class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-full font-semibold transition-all shadow-lg flex items-center justify-center gap-2"
          >
           Consultar
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
          </a>
          </div>
        </div>

        <!-- Card 3: Parque / Quintas -->
        <div class="bg-white border-green-100 border-2 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 flex flex-col">
          <h3 class="text-xl font-bold text-gray-900 mb-4">Parque / Quintas</h3>
          <div class="mb-6">
            <span class="text-3xl font-bold text-green-600">$90.000</span>
            <span class="text-gray-600">/mes</span>
          </div>
          <p class="text-gray-600 mb-6 md:text-lg">2.500 a 4.000 m² de tu jardín</p>
          <div class="mt-auto">
            <a 
            href="https://wa.me/5491170985242" 
            target="_blank" 
            rel="noopener noreferrer"
            class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-full font-semibold transition-all shadow-lg flex items-center justify-center gap-2"
          >
           Consultar
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
          </a>
          </div>
        </div>

        <!-- Card 4: Campo de Deportes / Consultar -->
        <div class="bg-white border-green-100 border-2 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 flex flex-col">
          <h3 class="text-xl font-bold text-gray-900 mb-4">Terrenos más grandes</h3>
   
          <p class="text-gray-600 mb-2">Canchas de Polo</p>
          <p class="text-gray-600 mb-2">Limpieza de Terrenos</p>
          <p class="text-gray-600 mb-6">Áreas Públicas</p>
          <div class="mt-auto">
            <a 
              href="https://wa.me/5491170985242" 
              target="_blank" 
              rel="noopener noreferrer"
              class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-full font-semibold transition-all shadow-lg flex items-center justify-center gap-2"
            >
             Consultar
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
              </svg>
            </a>
          </div>
        </div>

    

      </div>

      <div class="flex flex-col md:flex-row gap-6 mt-8 w-full">
        <p class="text-gray-600 text-lg"><em>Podes cancelar cuando quieras. <br>
          Sin contratos. Sin permanencia</em></p>

        <p class="text-gray-600 md:ml-auto md:text-right text-base opacity-80"><strong>IMPORTANTE:</strong>  No necesitas saber los metros exactos de tu jardín.
          Elegí la categoría que mejor lo represente. <br>
          Si en la primera visita vemos que corresponde a otra categoría, lo ajustamos sin problema. </p>
      </div>
    </div>
  </section>



<!-- Características principales -->
<section class="py-20 bg-gray-100" id="como-funciona">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-16">
      <h2 class="text-2xl md:text-4xl font-bold text-gray-900">¿Cómo funciona?</h2>
      <p class="mt-4 text-xl text-gray-600">Simple, profesional y confiable</p>
    </div>
    
    <div class="grid md:grid-cols-4 gap-12">
      <div class="text-center">
        <div class="bg-primary-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
          </svg>
        </div>
        <h6 class=" font-bold text-gray-900 mb-4 lg:text-xl">Elegís la categoría y el plan <br> que mejor se adapta a tu jardín. </h6>
        <!-- <p class="text-gray-600">Mantenimiento regular según tu plan. Sin preocupaciones, sin llamadas.</p> -->
      </div>

      <div class="text-center">
        <div class="bg-primary-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
        </div>
        <h6 class=" font-bold text-gray-900 mb-4 lg:text-xl">Elegís pago mensual o anual </h6>
        <!-- <p class="text-gray-600">Después de cada visita, recibes un reporte completo con fotos y recomendaciones.</p> -->
      </div>

      <div class="text-center">
        <div class="bg-primary-100 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
          <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
        <h6 class=" font-bold text-gray-900 mb-4 lg:text-xl">Al instante te agendamos <br> la primera visita y listo</h6>
            <!-- <p class="text-gray-600">Accede a todo el historial, reportes y estado de tu jardín desde cualquier lugar.</p> -->
      </div>

      <div class="text-center">
        <a
          href="https://wa.me/5491170985242"
          target="_blank"
          rel="noopener noreferrer"
          class="bg-green-600 hover:bg-green-700 mb-4 text-white w-20 h-20 rounded-full font-semibold transition-all shadow-lg flex items-center justify-center mx-auto"
        >
          <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
          </svg>
        </a>
         <h6 class=" font-bold text-gray-900 mb-4 lg:text-xl"> Si necesitas mas información <br> mandanos un mensaje al WhatsApp</h6> 
      </div>
    </div>
  </div>
</section>

<!-- Beneficios del servicio -->
<section id="beneficios" class="py-20 bg-gray-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid md:grid-cols-2 gap-12 items-center">
      <div>
        <h2 class="text-3xl lg:text-[34px] font-bold text-gray-900 mb-6">Servicios complementarios  <span class="text-green-600">para tu jardín</span></h2>
     

              <p class="mt-2 text-gray-600 lg:text-xl">Además del corte mensual incluido, los suscriptores pueden acceder a servicios complementarios para el mantenimiento del jardín, con prioridad y un descuento del 30%. </p>
          
      </div>

      <div class="bg-primary-600 rounded-2xl p-8 text-white">
        <ul class="space-y-4 mb-8">
            <li class="flex items-center lg:text-xl">
              <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Recolección y retiro de pasto
            </li>
            <li class="flex items-center lg:text-xl">
              <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Fertilización de tu jardín
            </li>
            <li class="flex items-center lg:text-xl">
              <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Cortes extras mensuales
            </li>
            <li class="flex items-center lg:text-xl">
              <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Desmalezado de canteros
            </li>
            <li class="flex items-center lg:text-xl">
              <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Resiembra o siembra de césped
            </li>
            <li class="flex items-center lg:text-xl">
              <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Limpieza de hojas
            </li>
            <li class="flex items-center lg:text-xl">
              <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Aireado del césped
            </li>
            <li class="flex items-center lg:text-xl">
              <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Corte de arbustos-cercos
            </li>
        </ul>

      </div>
    </div>
  </div>
</section>



  <section id="programa-cliente-amigo">
    <div class="px-4 sm:px-6 lg:px-8 bgAmigo">
      <div class="text-center mb-12 max-w-7xl mx-auto py-12 gap-4 flex flex-col items-center justify-center">
        <h2 class="text-2xl md:text-5xl font-bold text-white">PROGRAMA CLIENTE AMIGO</h2>
        <p class="text-white text-xl">Recomendá <strong>CESPED365 </strong>a un amigo y obtené descuento de <em>2 cortes exclusivos</em> en tu suscripción. </p>
      </div>
    </div>
  </section>


<!-- Preguntas Frecuentes -->
<section id="faq" class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <h2 class="text-2xl md:text-5xl font-bold text-gray-900">Preguntas frecuentes</h2>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
      {#each faqs as faq, index}
        <div class="border border-gray-200 rounded-2xl overflow-hidden transition-all duration-300">
          <button
            on:click={() => toggleFaq(index)}
            class="w-full px-6 py-5 flex items-center justify-between text-left transition-all duration-300 {openFaq === index ? 'bg-[#0b3d2c] text-white' : 'bg-white text-gray-900 hover:bg-gray-50'}"
          >
            <span class="font-semibold text-lg pr-4">{faq.question}</span>
            <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center">
              {#if openFaq === index}
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                </svg>
              {:else}
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              {/if}
            </div>
          </button>
          
          {#if openFaq === index}
            <div class="px-6 py-5 bg-[#0b3d2c] text-white border-t border-green-800">
              <div class="prose prose-invert max-w-none">
                {#each faq.answer.split('\n') as paragraph}
                  {#if paragraph.trim()}
                    <p class="mb-3 last:mb-0 leading-relaxed">{paragraph}</p>
                  {/if}
                {/each}
              </div>
            </div>
          {/if}
        </div>
      {/each}
    </div>
  </div>
</section>


  <!-- Quiénes Somos / About Us -->
  <section id="about" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid lg:grid-cols-2 gap-16 items-center">
        <!-- Left side - Text Content -->
        <div>
          <!-- Small heading with icon -->
          <div class="flex items-center gap-2 mb-4">
         
<svg width="34" height="35" viewBox="0 0 34 35" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M22.4073 26.801C20.6585 28.0481 18.5569 28.7037 16.4092 28.6724C14.5331 28.6427 12.6782 28.2697 10.9366 27.5717C10.5355 28.5062 10.2078 29.4706 9.95624 30.456C9.89057 30.7238 9.72336 30.9556 9.48993 31.1024C9.2565 31.2492 8.97515 31.2995 8.70532 31.2426C8.4355 31.1858 8.19833 31.0263 8.04395 30.7978C7.88957 30.5693 7.83006 30.2898 7.87799 30.0182C9.72576 23.3334 13.7974 17.4783 19.421 13.4192C19.6464 13.2501 19.9298 13.1775 20.2088 13.2173C20.4877 13.2572 20.7394 13.4062 20.9085 13.6317C21.0776 13.8571 21.1502 14.1405 21.1103 14.4194C21.0705 14.6984 20.9214 14.9501 20.696 15.1192C16.9745 17.8987 13.9414 21.4961 11.8305 25.6337C14.0603 26.4837 17.9562 27.3832 21.1663 25.0755C27.8941 20.2362 25.762 10.5377 24.5366 6.60216C23.5147 7.67178 22.256 8.48651 20.8617 8.98074C19.6661 9.39016 18.5738 9.67774 17.517 9.95824C15.8883 10.3152 14.3086 10.8674 12.8122 11.603C7.59183 14.4165 7.58191 20.1951 8.84558 23.3939C8.93946 23.6537 8.92848 23.9398 8.81496 24.1916C8.70145 24.4435 8.49433 24.6412 8.2375 24.7429C7.98068 24.8446 7.69435 24.8423 7.4392 24.7364C7.18406 24.6306 6.98016 24.4295 6.87074 24.1759C5.32658 20.2716 5.36341 13.2038 11.805 9.73158C13.4478 8.91509 15.1836 8.30096 16.9744 7.90266C17.9944 7.63207 19.0498 7.35299 20.1676 6.97049C21.6077 6.45508 22.8528 5.50638 23.7319 4.25474C23.8706 4.04882 24.0643 3.88588 24.2909 3.7844C24.5175 3.68291 24.768 3.64694 25.014 3.68057C25.26 3.7142 25.4916 3.81607 25.6827 3.97464C25.8737 4.13322 26.0165 4.34215 26.0949 4.57774C27.0427 7.13907 31.3649 20.3594 22.4073 26.801Z" fill="#2A7D2E"/>
  </svg>
  
            <span class="text-green-600 font-semibold text-sm uppercase tracking-wider">Cesped 365</span>
          </div>

          <!-- Main title -->
          <h2 class="text-2xl md:text-5xl font-bold text-gray-900 mb-6">
           Quienes somos
          </h2>

          <!-- Paragraph 1 -->
          <p class="text-gray-600 text-lg mb-6 leading-relaxed">
            Somos un equipo que se dedica al mantenimiento de jardines, pensamos este servicio para que tu casa tenga el jardín cuidado todo el año sin que tengas que estar pendiente. <br><br>
            Armamos CÉSPED 365 porque muchas veces el jardín queda para después y termina descuidándose. <br>
            Por eso trabajamos de forma distinta, con fechas claras y mantenimiento constante. <br><br>
            Vamos una vez por mes, en una fecha acordada, hacemos el corte, los bordes y dejamos todo prolijo. Sin vueltas. <br>
            Además, en cada visita observamos el estado general del jardín y su evolución con el tiempo, para poder acompañarlo mejor según la época del año. <br><br>
            Contamos con las mejores máquinas que nos permiten trabajar rápido, tratando de molestar lo menos posible. <br>
            La idea es simple: que no tengas que pensar en el jardín ni estar escribiéndole a nadie para que venga, solo disfrutar tu casa como corresponde. </p>
        </div>

        <!-- Right side - Image Grid -->
        <div class="relative">
          <div class="grid">
            <img src="varias.webp"   class="w-full h-full object-cover" alt="varias imagenes">
          </div>
        </div>
      </div>
    </div>
  </section>

  

  

  <!-- Footer -->
  <footer class="bg-white">
    <!-- SVG de pasto en la parte superior -->
    <div class="pasto">
      <img src="/pasto.svg" alt="Pasto decorativo" class="w-full h-auto" />
    </div>

    <div class="bg-[#1b3c2d]" style="margin-top:-5px;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <!-- Footer Grid Layout -->
      <div class="grid md:grid-cols-3 gap-8 lg:gap-12 mb-12">
        <!-- Columna 1: Información de la empresa -->
        <div>
          <div class="flex items-center gap-3 mb-4">
            <img src="/logo.png" alt="Logo" style="width: 200px;">
            
          </div>
          <p class="text-gray-300 mb-4">
            Servicio de mantenimiento de jardines por suscripción mensual. 
          </p>
          <p class="text-sm">
            <span class="inline-flex items-center" style="color:#f9fd82 !important">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Tandil y alrededores
            </span>
          </p>
        </div>

        <!-- Columna 2: Navegación -->
        <div>
          <h4 class="text-white text-lg font-semibold mb-4">Navegación</h4>
          <ul class="space-y-2">
            <li>
              <a href="#home" on:click={(e) => smoothScrollToSection(e, 'home')} class="text-gray-300 hover:text-white transition-colors cursor-pointer">Inicio</a>
            </li>
            <li>
              <a href="#beneficios" on:click={(e) => smoothScrollToSection(e, 'beneficios')} class="text-gray-300 hover:text-white transition-colors cursor-pointer">Beneficios</a>
            </li>
            <li>
              <a href="#categorias" on:click={(e) => smoothScrollToSection(e, 'categorias')} class="text-gray-300 hover:text-white transition-colors cursor-pointer">Planes</a>
            </li>
            <li>
              <a href="#como-funciona" on:click={(e) => smoothScrollToSection(e, 'como-funciona')} class="text-gray-300 hover:text-white transition-colors cursor-pointer">Como funciona</a>
            </li>
            <li>
              <a href="#faq" on:click={(e) => smoothScrollToSection(e, 'faq')} class="text-gray-300 hover:text-white transition-colors cursor-pointer">FAQ</a>
            </li>
            <li>
              <a href="#about" on:click={(e) => smoothScrollToSection(e, 'about')} class="text-gray-300 hover:text-white transition-colors cursor-pointer">Quiénes somos</a>
            </li>
          </ul>
        </div>

        <!-- Columna 3: Contacto y acción -->
        <div>
          <h4 class="text-white text-lg font-semibold mb-4">Contacto</h4>
          <div class="space-y-3 mb-6">
            <div class="flex gap-6">
              <a
                href="https://wa.me/5491170985242"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 text-gray-300 hover:text-white transition-colors"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                WhatsApp
              </a>

              <a
                href="mailto:nacho@cesped365.com"
                class="inline-flex items-center gap-2 text-gray-300 hover:text-white transition-colors"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                nacho@cesped365.com
              </a>

              <a
                href="https://www.instagram.com/cesped365/?hl=en"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 text-gray-300 hover:text-white transition-colors"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
                Instagram
              </a>
            </div>

            <p class="text-gray-400 text-sm">
              Servicio disponible los 365 días del año.
            </p>
          </div>

          <!-- Call to action para jardineros -->
          <div class="bg-green-600 rounded-lg p-4">
            <p class="text-white text-sm mb-2">¿Sos jardinero?</p>
            <p class="text-white font-semibold text-sm mb-3">Sumate al equipo CESPED 365</p>
            <a
              href="https://wa.me/5491170985242"
              target="_blank"
              rel="noopener noreferrer"
              class="inline-flex items-center gap-2 bg-white text-green-600 px-4 py-2 rounded-full font-semibold text-sm hover:bg-gray-100 transition-colors"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
              </svg>
              Contactar
            </a>
          </div>
        </div>
      </div>

      <!-- Bottom section -->
      <div class="border-t border-gray-700 pt-8">
        <div class="text-center">
          <p class="text-gray-400 text-sm">
            © 2025 CESPED 365. Todos los derechos reservados.
          </p>
        </div>
      </div>
    </div>
     
    </div>
  </footer>

  <!-- Floating WhatsApp Button -->
  {#if showFloatingWhatsApp}
    <a
      href="https://wa.me/5491170985242"
      target="_blank"
      rel="noopener noreferrer"
      class="fixed bottom-5 left-5 bg-green-600 hover:bg-green-700 text-white p-3 md:p-4 rounded-full shadow-lg transition-all duration-300 ease-in-out transform hover:scale-110 z-40"
      aria-label="Contactar por WhatsApp"
    >
      <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
      </svg>
    </a>
  {/if}

  <!-- Scroll to Top Button -->
  {#if showScrollTop}
    <button
      on:click={scrollToTop}
      class="fixed bottom-5 right-5 bg-green-600 hover:bg-green-700 text-white p-3 rounded-full shadow-lg transition-all duration-300 ease-in-out transform hover:scale-110 z-50"
      aria-label="Volver arriba"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
      </svg>
    </button>
  {/if}
</div>
