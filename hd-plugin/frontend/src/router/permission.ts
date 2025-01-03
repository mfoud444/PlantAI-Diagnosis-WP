import type {Router } from 'vue-router'
export function setupPageGuard(router: Router) {
  router.beforeEach(async (to, from, next) => {
    if (to.meta.requiresAuth) {
      next({ name: 'auth' })
     } else {
      next()
    }
  })
}
