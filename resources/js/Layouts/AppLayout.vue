<template>
  <div>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <!-- Pakai Link, bukan RouterLink -->
        <Link class="navbar-brand" href="/">MyApp</Link>
        
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav ms-auto">
            <!-- Home -->
            <li class="nav-item">
              <Link 
                class="nav-link" 
                :class="{ active: $page.url === '/' }" 
                href="/"
              >
                Home
              </Link>
            </li>

            <!-- About -->
            <li class="nav-item">
              <Link 
                class="nav-link" 
                :class="{ active: $page.url.startsWith('/about') }" 
                href="/about"
              >
                About
              </Link>
            </li>


            <li class="nav-item">
              <Link 
                class="nav-link" 
                :class="{ active: $page.url.startsWith('/category') }" 
                href="/category"
              >
                Category
              </Link>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <!-- Content -->
    <main class="container mt-4">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
