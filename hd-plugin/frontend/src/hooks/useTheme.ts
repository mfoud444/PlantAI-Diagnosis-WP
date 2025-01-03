import type { GlobalThemeOverrides } from 'naive-ui'
import { computed, watch } from 'vue'
import { darkTheme, useOsTheme } from 'naive-ui'
import { useAppStore } from '@/store'

export function useTheme() {
  const appStore = useAppStore()

  const OsTheme = useOsTheme()

  const isDark = computed(() => {
    if (appStore.theme === 'auto')
      return OsTheme.value === 'dark'
    else
      return appStore.theme === 'dark'
  })

  const theme = computed(() => {
    return isDark.value ? darkTheme : undefined
  })


const themeVars = {
  
    "fontFamily": '"Calibri", sans-serif',
    "fontFamilyMono":"'Calibri', sans-serif",
    "fontWeight": "400",
    "fontWeightStrong": "500",
    "cubicBezierEaseInOut": "cubic-bezier(.4, 0, .2, 1)",
    "cubicBezierEaseOut": "cubic-bezier(0, 0, .2, 1)",
    "cubicBezierEaseIn": "cubic-bezier(.4, 0, 1, 1)",
    "borderRadius": "0.5rem",
    "borderRadiusSmall": "2px",
    "fontSize": "1rem",
    "fontSizeMini": "12px",
    "fontSizeTiny": "12px",
    "fontSizeSmall": "14px",
    "fontSizeMedium": "1rem",
    "fontSizeLarge": "15px",
    "fontSizeHuge": "16px",
    "lineHeight": "1.6",
    "heightMini": "16px",
    "heightTiny": "22px",
    "heightSmall": "28px",
    "heightMedium": "34px",
    "heightLarge": "44px",
    "heightHuge": "46px",
    "baseColor":  "#fff",
    "primaryColor": "#208000",
    "primaryColorHover": "#36ad6a",
    "primaryColorPressed": "#0c7a43",
    "primaryColorSuppl": "#36ad6a",
    "infoColor": "#2080f0",
    "infoColorHover": "#4098fc",
    "infoColorPressed": "#1060c9",
    "infoColorSuppl": "#4098fc",
    "successColor": "#18a058",
    "successColorHover": "#36ad6a",
    "successColorPressed": "#0c7a43",
    "successColorSuppl": "#36ad6a",
    "warningColor": "#f0a020",
    "warningColorHover": "#fcb040",
    "warningColorPressed": "#c97c10",
    "warningColorSuppl": "#fcb040",
    "errorColor": "#d03050",
    "errorColorHover": "#de576d",
    "errorColorPressed": "#ab1f3f",
    "errorColorSuppl": "#de576d",
    "textColorBase": "#000",
    "textColor1": "rgb(31, 34, 37)",
    "textColor2": "rgb(51, 54, 57)",
    "textColor3": "rgb(118, 124, 130)",
    "textColorDisabled": "rgba(194, 194, 194, 1)",
    "placeholderColor": "rgba(194, 194, 194, 1)",
    "placeholderColorDisabled": "rgba(209, 209, 209, 1)",
    "iconColor": "rgba(194, 194, 194, 1)",
    "iconColorHover": "rgba(146, 146, 146, 1)",
    "iconColorPressed": "rgba(175, 175, 175, 1)",
    "iconColorDisabled": "rgba(209, 209, 209, 1)",
    "opacity1": "0.82",
    "opacity2": "0.72",
    "opacity3": "0.38",
    "opacity4": "0.24",
    "opacity5": "0.18",
    "dividerColor": "rgb(239, 239, 245)",
    "borderColor": "#000",
    "closeIconColor": "rgba(102, 102, 102, 1)",
    "closeIconColorHover": "rgba(102, 102, 102, 1)",
    "closeIconColorPressed": "rgba(102, 102, 102, 1)",
    "closeColorHover": "rgba(0, 0, 0, .09)",
    "closeColorPressed": "rgba(0, 0, 0, .13)",
    "clearColor": "rgba(194, 194, 194, 1)",
    "clearColorHover": "rgba(146, 146, 146, 1)",
    "clearColorPressed": "rgba(175, 175, 175, 1)",
    "scrollbarColor": "rgba(0, 0, 0, 0.25)",
    "scrollbarColorHover": "rgba(0, 0, 0, 0.4)",
    "scrollbarWidth": "5px",
    "scrollbarHeight": "5px",
    "scrollbarBorderRadius": "5px",
    "progressRailColor": "rgba(235, 235, 235, 1)",
    "railColor": "rgb(219, 219, 223)",
    "popoverColor": "#fff",
    "tableColor": "#fff",
    "cardColor": "#eff6ff",
    "modalColor": "#fff",
    "bodyColor": "#fff",
    "tagColor": "#eee",
    "avatarColor": "rgba(204, 204, 204, 1)",
    "invertedColor": "rgb(0, 20, 40)",
    "inputColor": "rgba(255, 255, 255, 1)",
    "codeColor": "#000",
    "tabColor": "rgb(247, 247, 250)",
    "actionColor": "rgb(250, 250, 252)",
    "tableHeaderColor": "#fecaca",
    "hoverColor": "rgb(243, 243, 245)",
    "tableColorHover": "rgba(20, 100, 300, 0.3)",
    "tableColorStriped": "rgba(0, 30, 300, 0.08)",
    "pressedColor": "rgb(237, 237, 239)",
    "opacityDisabled": "0.5",
    "inputColorDisabled": "rgb(250, 250, 252)",
    "buttonColor2": "rgba(46, 51, 56, .05)",
    "buttonColor2Hover": "rgba(46, 51, 56, .09)",
    "buttonColor2Pressed": "rgba(46, 51, 56, .13)",
    "boxShadow1": "0 1px 2px -2px rgba(0, 0, 0, .08), 0 3px 6px 0 rgba(0, 0, 0, .06), 0 5px 12px 4px rgba(0, 0, 0, .04)",
    "boxShadow2": "0 3px 6px -4px rgba(0, 0, 0, .12), 0 6px 16px 0 rgba(0, 0, 0, .08), 0 9px 28px 8px rgba(0, 0, 0, .05)",
    "boxShadow3": "0 6px 16px -9px rgba(0, 0, 0, .08), 0 9px 28px 0 rgba(0, 0, 0, .05), 0 12px 48px 16px rgba(0, 0, 0, .03)"
  }

  const themeOverrides = computed<GlobalThemeOverrides>(() => {
 
    return {
      common: themeVars,
  
    }
  })

  watch(
    () => isDark.value,
    (dark) => {
      if (dark)
        document.documentElement.classList.add('dark')
      else
        document.documentElement.classList.remove('dark')
    },
    { immediate: true },
  )

  return { theme, themeOverrides }
}
