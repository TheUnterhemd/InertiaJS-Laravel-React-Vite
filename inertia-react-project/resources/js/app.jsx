import React from "react";
import { createRoot } from "react-dom/client"; // Ändere den Importpfad hier
import { createInertiaApp } from "@inertiajs/inertia-react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { BrowserRouter as Router, Route, Routes, Link } from "react-router-dom";
import Home from "./Pages/Home";
import Anwendung from "./Pages/Anwendung";

const Pages = import.meta.glob("./Pages/**/*.jsx");

createInertiaApp({
  resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, Pages),
  setup({ el, App, props }) {
    createRoot(el).render(
      <Router>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/app" element={<Anwendung />} />
        </Routes>
      </Router>
    );
  },
});