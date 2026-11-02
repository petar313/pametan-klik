import { createApp } from "vue";
import "./app.css";

// Vue is mounted only on the parts of a page that need interaction:
// <div data-vue="ComponentName" data-props='{"...": "..."}'></div>
const components = import.meta.glob("./vue/*.vue", { eager: true });

document.querySelectorAll("[data-vue]").forEach((el) => {
    const component = components[`./vue/${el.dataset.vue}.vue`];
    if (component) {
        createApp(component.default, JSON.parse(el.dataset.props || "{}")).mount(el);
    }
});
