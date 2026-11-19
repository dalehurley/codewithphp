/// <reference types="astro/client" />
/// <reference types="@astrojs/starlight/virtual" />

import type { StarlightRouteData } from '@astrojs/starlight/route-data';

declare global {
  namespace App {
    interface Locals {
      starlightRoute: StarlightRouteData;
    }
  }
}
