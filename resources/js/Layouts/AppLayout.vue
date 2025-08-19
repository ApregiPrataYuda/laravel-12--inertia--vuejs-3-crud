<template>
  <div class="d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <Link class="navbar-brand" href="/">MyApp</Link>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <Link class="nav-link" :class="{ active: $page.url === '/' }" href="/">Home</Link>
            </li>
            <li class="nav-item">
              <Link class="nav-link" :class="{ active: $page.url.startsWith('/about') }" href="/about">About</Link>
            </li>
            <li class="nav-item">
              <Link class="nav-link" :class="{ active: $page.url.startsWith('/category') }" href="/category">Category</Link>
            </li>
             <li class="nav-item">
              <Link class="nav-link" :class="{ active: $page.url.startsWith('/tags') }" href="/tags">Tags</Link>
            </li>

             <li class="nav-item">
              <Link class="nav-link" :class="{ active: $page.url.startsWith('/tag') }" href="/tag">tags New</Link>
            </li>

            
          </ul>
        </div>
      </div>
    </nav>

    <!-- Konten utama -->
    <main class="flex-fill container mt-4">
      <slot />
    </main>

     <!-- Notifications Global -->
    <notifications position="top right" />

    <!-- Footer -->
    <FooterLayout />
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import FooterLayout from './FooterLayout.vue';
</script>
