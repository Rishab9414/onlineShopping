import './bootstrap';
import Alpine from 'alpinejs';
import { Shop, registerShopAlpine } from './shop';

window.Alpine = Alpine;
window.Shop = Shop;

registerShopAlpine(Alpine);

Alpine.start();
