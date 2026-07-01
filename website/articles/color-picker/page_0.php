<div id="javascript_content" style="display: none">
    <div id="color_picker_container" style="padding: 5px; border-color: var(--pico-8-dark-grey); border-style: solid; border-width: 4px 5px 4px 5px;">
        <!--canvas-->
        <table style="display: inline-block">
            <tr>
                <td id="canvas_label_x" class="label" title="x">x</td>
                <td><canvas id="canvas_xy" class="canvas" width="256" height="256"></canvas></td>
                <td><canvas id="canvas_z" class="canvas" width="20" height="256"></canvas></td>
            </tr>
            <tr>
                <td></td>
                <td id="canvas_label_y" style="text-align: center" class="label" title="y">y</td>
                <td id="canvas_label_z" style="text-align: center" class="label" title="z">z</td>
            </tr>
        </table>

        <!--sliders-->
        <table style="display: inline-block; vertical-align: top;">
            <tr>
                <td colspan="3">
                    <select id="clamp_dropdown">
                        <option value="clamp">clamp invalid colors</option>
                        <option value="hide">hide invalid colors</option>
                    </select>
                </td>
                <td>
                    <select id="format_dropdown" class="text_field">
                        <option value="normalized">0..1</option>
                        <option value="byte">0..255</option>
                    </select>
                </td>
                <td></td>
            </tr>
            <tr>
                <td><input type="radio" id="radio_r" name="z_coord" value="r"></td>
                <td><span class="label" title="Red">R</span></td>
                <td><input type="range" id="slider_r" class="slider" min="0" max="1" step="0.004"></td>
                <td>
                    <input type="number" id="byte_field_r" class="text_field" min="0" max="255" step="1" oninput="handle_rgb_input(this, true)">
                    <input type="number" id="normalized_field_r" class="text_field" min="0" max="1" step="0.004" oninput="handle_rgb_input(this, false)">
                </td>
                <td><span class="label" id="warning_r" title="This color is outside the sRGB space">&#128721;</span></td>
            </tr>
            <tr>
                <td><input type="radio" id="radio_g" name="z_coord" value="g"></td>
                <td><span class="label" title="Green">G</span></td>
                <td><input type="range" id="slider_g" class="slider" min="0" max="1" step="0.004"></td>
                <td>
                    <input type="number" id="byte_field_g" class="text_field" min="0" max="255" oninput="handle_rgb_input(this, true)">
                    <input type="number" id="normalized_field_g" class="text_field" min="0" max="1" step="0.004" oninput="handle_rgb_input(this, false)">
                </td>
                <td><span class="label" id="warning_g" title="This color is outside the sRGB space">&#128721;</span></td>
            </tr>
            <tr>
                <td><input type="radio" id="radio_b" name="z_coord" value="b"></td>
                <td><span class="label" title="Blue">B</span></td>
                <td><input type="range" id="slider_b" class="slider" min="0" max="1" step="0.004"></td>
                <td>
                    <input type="number" id="byte_field_b" class="text_field" min="0" max="255" oninput="handle_rgb_input(this, true)">
                    <input type="number" id="normalized_field_b" class="text_field" min="0" max="1" step="0.004" oninput="handle_rgb_input(this, false)">
                </td>
                <td><span class="label" id="warning_b" title="This color is outside the sRGB space">&#128721;</span></td>
            </tr>
            <tr>
                <td><input type="radio" id="radio_l" name="z_coord" value="l"></td>
                <td><span class="label" title="OkLab Luminocity">L</span></td>
                <td><input type="range" id="slider_l" class="slider" min="0" max="1" step="0.004"></td>
                <td>
                    <input type="number" id="normalized_field_l" class="text_field" min="0" max="1" step="0.004" oninput="handle_lch_input(this)">
                </td>
                <td></td>
            </tr>
            <tr>
                <td><input type="radio" id="radio_c" name="z_coord" value="c"></td>
                <td><span class="label" title="OkLab Chroma">C</span></td>
                <td><input type="range" id="slider_c" class="slider" min="0" max="0.4" step="0.004"></td>
                <td>
                    <input type="number" id="normalized_field_c" class="text_field" min="0" max="0.4" step="0.004" oninput="handle_lch_input(this)">
                </td>
                <td></td>
            </tr>
            <tr>
                <td><input type="radio" id="radio_h" name="z_coord" value="h"></td>
                <td><span class="label" title="OkLab Hue">H</span></td>
                <td><input type="range" id="slider_h" class="slider" min="-3.141" max="3.141" step="0.009"></td>
                <td>
                    <input type="number" id="normalized_field_h" class="text_field" min="-3.141" max="3.141" step="0.009" oninput="handle_lch_input(this)">
                </td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2"><span class="label">Hex Code</span></td>
                <td><input type="text" id="hex_field" class="text_field" oninput="handle_hex_input(this)"></td>
                <td></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2"><span id="background_label" class="label selectable">Background</span></td>
                <td><input type="text" id="background_hex_field" class="text_field" oninput="handle_background_hex_input(this)"></td>
                <td></td>
            </tr>
        </table>

        <br>

        <!--color scheme generator-->
        <table style="display: inline-block; vertical-align: top;">
            <tr style="height: 25px">
                <td colspan="2">
                    <select id="interpolation_dropdown">
                        <option value="none">no interpolation</option>
                        <option value="oklab">interpolate OkLab</option>
                        <option value="rgb">interpolate sRGB</option>
                    </select>
                </td>
                <td><button class="no-style" onclick="hide_ui(true)">Hide UI</button></td>
            </tr>
            <tr>
                <!--<td colspan="4"><canvas id="canvas_interpolation" class="canvas" width="256" height="32" style="width: 100%; height: 32px; position: relative; z-index: 10000"></canvas></td>-->
            </tr>
        </table>
        
        <canvas id="canvas_interpolation" class="canvas" style="width: 100%; height: 32px; position: relative; z-index: 10000"></canvas>

        <br>

        <!--color scheme colors-->
        <table style="display: inline-block; vertical-align: top;">
            <tr>
                <td colspan="2" id="color_container">
                    <!--color html here-->
                </td>
            </tr>
        </table>

        <!--other-->
        <div id="background_overlay" style="display: none;">
        </div>

        <!--<div style="text-align: right">
            <button class="no-style" onclick="reset_everything()">Reset Colors</Button>
        </div>-->
    
    </div>
    
    <!--description-->
    <p>This color picker implements <a href="https://en.wikipedia.org/wiki/Oklab_color_space" target="_blank" rel="noopener noreferrer">Oklab</a>, a <a href="https://bottosson.github.io/posts/oklab/" target="_blank" rel="noopener noreferrer">perceptual color space</a> invented by Björn Ottosson.</p>

    <p>Besides RGB (red, green, blue), this color picker also exposes LCH:</p>

    <ul>
    <li><u>L</u>uminocity: How bright the color is.<br>Goes from black to white.</li>
    <li><u>C</u>hroma: How strong or saturated the color is.<br>Goes from colorless, grey, to colorful.</li>
    <li><u>H</u>ue: The actual color value.</li>
    </ul>

    <p>LCH is the polar form of Oklab. This means hue represents an angle and wraps around. The angle is in radians, which goes from negative PI to positive PI, or -3.141 to +3.141.</p>
    
    <p>Note that Oklab is a larger color space than RGB. This means not every Oklab color can be represented as an RGB color. You can choose whether you want to clamp or hide invalid Oklab colors. In case of hiding, invalid colors will be drawn as magenta #ff00ff. </p>
    
    <p>If you selected an invalid RGB color, it will be marked by this emoji: &#128721; Clicking on the emoji will clamp the color into valid range.</p>
    
    <p>GitHub: <a href="https://github.com/Rismosch/ris_color_picker" target="_blank" rel="noopener noreferrer">https://github.com/Rismosch/ris_color_picker</a></p>

</div>

<noscript>
    <p>You need to enable JavaScript for this page to work &#9757;&#129299;</p>
</noscript>

<script>
    // enable color picker
    const javascript_content = document.getElementById("javascript_content");
    javascript_content.style.display = "inline";

    // cookie
    const cookie_name = "ris_color_picker_cookie";
    let cookie;

    // get elements
    const canvas_xy = document.getElementById("canvas_xy");
    const canvas_z = document.getElementById("canvas_z");
    const canvas_label_x = document.getElementById("canvas_label_x");
    const canvas_label_y = document.getElementById("canvas_label_y");
    const canvas_label_z = document.getElementById("canvas_label_z");
    const clamp_dropdown = document.getElementById("clamp_dropdown");
    const format_dropdown = document.getElementById("format_dropdown");
    const slider_r = document.getElementById("slider_r");
    const slider_g = document.getElementById("slider_g");
    const slider_b = document.getElementById("slider_b");
    const slider_l = document.getElementById("slider_l");
    const slider_c = document.getElementById("slider_c");
    const slider_h = document.getElementById("slider_h");
    const byte_field_r = document.getElementById("byte_field_r");
    const byte_field_g = document.getElementById("byte_field_g");
    const byte_field_b = document.getElementById("byte_field_b");
    const normalized_field_r = document.getElementById("normalized_field_r");
    const normalized_field_g = document.getElementById("normalized_field_g");
    const normalized_field_b = document.getElementById("normalized_field_b");
    const normalized_field_l = document.getElementById("normalized_field_l");
    const normalized_field_c = document.getElementById("normalized_field_c");
    const normalized_field_h = document.getElementById("normalized_field_h");
    const hex_field = document.getElementById("hex_field");
    const background_label = document.getElementById("background_label");
    const background_hex_field = document.getElementById("background_hex_field");
    const warning_r = document.getElementById("warning_r");
    const warning_g = document.getElementById("warning_g");
    const warning_b = document.getElementById("warning_b");

    const interpolation_dropdown = document.getElementById("interpolation_dropdown");
    const canvas_interpolation = document.getElementById("canvas_interpolation");

    const background_actual = document.getElementById("color_picker_container");
    const background_overlay = document.getElementById("background_overlay");

    const color_container = document.getElementById("color_container");

    const labels = document.querySelectorAll(".label");

    // initialize canvas
    const ctx_xy = canvas_xy.getContext('2d');
    const ctx_z = canvas_z.getContext('2d');
    const ctx_interpolation = canvas_interpolation.getContext('2d');

    let is_dragging_xy = false;
    let is_dragging_z = false;
    let is_touching_xy = false;
    let is_touching_z = false;

    document.body.addEventListener('mousemove', (e) => {
        if (is_dragging_xy) {
            drag_xy(e);
        } else if (is_dragging_z) {
            drag_z(e);
        }
    });

    document.body.addEventListener('mouseup', (e) => {
        is_dragging_xy = false;
        is_dragging_z = false;
    });

    canvas_xy.addEventListener('mousedown', (e) => {
        is_dragging_xy = true;
        drag_xy(e);
    });

    canvas_z.addEventListener('mousedown', (e) => {
        is_dragging_z = true;
        drag_z(e);
    });
    
    document.body.addEventListener('touchmove', (e) => {
        if (is_touching_xy) {
            e.preventDefault();
            drag_xy(e.touches[0]);
        } else if (is_touching_z) {
            e.preventDefault();
            drag_z(e.touches[0]);
        }
    });

    document.body.addEventListener('touchend', (e) => {
        is_touching_xy = false;
        is_touching_z = false;
    });

    canvas_xy.addEventListener('touchstart', (e) => {
        is_touching_xy = true;
        e.preventDefault();
        drag_xy(e.touches[0]);
    });

    canvas_z.addEventListener('touchstart', (e) => {
        is_touching_z = true;
        e.preventDefault();
        drag_z(e.touches[0]);
    });

    function drag_xy(e) {
        const rect_xy = canvas_xy.getBoundingClientRect();
        const x = e.clientX - rect_xy.left;
        const y = canvas_xy.height - e.clientY + rect_xy.top;
        let normalized_x = x / canvas_xy.width;
        let normalized_y = y / canvas_xy.height;

        if (normalized_x < 0) {
            normalized_x = 0;
        }
        if (normalized_x > 1) {
            normalized_x = 1;
        }
        if (normalized_y < 0) {
            normalized_y = 0;
        }
        if (normalized_y > 1) {
            normalized_y = 1;
        }

        switch(cookie.selected_z) {
            case "r":
                byte_field_g.value = Math.round(normalized_x * 255);
                byte_field_b.value = Math.round(normalized_y * 255);
                normalized_field_g.value = normalized_x.toFixed(3);
                normalized_field_b.value = normalized_y.toFixed(3);
                update_lch();
                break;
            case "g":
                byte_field_r.value = Math.round(normalized_x * 255);
                byte_field_b.value = Math.round(normalized_y * 255);
                normalized_field_r.value = normalized_x.toFixed(3);
                normalized_field_b.value = normalized_y.toFixed(3);
                update_lch();
                break;
            case "b":
                byte_field_r.value = Math.round(normalized_x * 255);
                byte_field_g.value = Math.round(normalized_y * 255);
                normalized_field_r.value = normalized_x.toFixed(3);
                normalized_field_g.value = normalized_y.toFixed(3);
                update_lch();
                break;
            case "l": {
                normalized_field_c.value = (normalized_x * 0.4).toFixed(3);
                normalized_field_h.value = (normalized_y * 2 * Math.PI - Math.PI).toFixed(3);
                update_rgb();
                break;
            }
            case "c": {
                normalized_field_l.value = (normalized_x).toFixed(3);
                normalized_field_h.value = (normalized_y * 2 * Math.PI - Math.PI).toFixed(3);
                update_rgb();
                break;
            }
            case "h": {
                normalized_field_l.value = (normalized_x).toFixed(3);
                normalized_field_c.value = (normalized_y * 0.4).toFixed(3);
                update_rgb();
                break;
            }
        }

        slider_r.value = normalized_field_r.value;
        slider_g.value = normalized_field_g.value;
        slider_b.value = normalized_field_b.value;
        slider_l.value = normalized_field_l.value;
        slider_c.value = normalized_field_c.value;
        slider_h.value = normalized_field_h.value;

        update_hex();
        update_canvas();
        update_color();
        save_cookie();
    }

    function drag_z(e) {
        const rect_z = canvas_z.getBoundingClientRect();
        const y = canvas_xy.height - e.clientY + rect_z.top;
        let normalized_y = y / canvas_xy.height;

        if (normalized_y < 0) {
            normalized_y = 0;
        }
        if (normalized_y > 1) {
            normalized_y = 1;
        }

        switch(cookie.selected_z) {
            case "r":
                byte_field_r.value = Math.round(normalized_y * 255);
                normalized_field_r.value = normalized_y.toFixed(3);
                update_lch();
                break;
            case "g":
                byte_field_g.value = Math.round(normalized_y * 255);
                normalized_field_g.value = normalized_y.toFixed(3);
                update_lch();
                break;
            case "b":
                byte_field_b.value = Math.round(normalized_y * 255);
                normalized_field_b.value = normalized_y.toFixed(3);
                update_lch();
                break;
            case "l": {
                normalized_field_l.value = (normalized_y).toFixed(3);
                update_rgb();
                break;
            }
            case "c": {
                normalized_field_c.value = (normalized_y * 0.4).toFixed(3);
                update_rgb();
                break;
            }
            case "h": {
                normalized_field_h.value = (normalized_y * 2 * Math.PI - Math.PI).toFixed(3);
                update_rgb();
                break;
            }
        }

        slider_r.value = normalized_field_r.value;
        slider_g.value = normalized_field_g.value;
        slider_b.value = normalized_field_b.value;
        slider_l.value = normalized_field_l.value;
        slider_c.value = normalized_field_c.value;
        slider_h.value = normalized_field_h.value;

        update_hex();
        update_canvas();
        update_color();
        save_cookie();
    }

    // initialize dropdowns
    clamp_dropdown.addEventListener("change", clamp_dropdown_change_callback)
    format_dropdown.addEventListener("change", format_dropdown_change_callback);
    interpolation_dropdown.addEventListener("change", interpolation_dropdown_change_callback);

    // initialize radio
    const radios = document.querySelectorAll('input[name="z_coord"]');
    radios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.checked) {
                cookie.selected_z = this.value;
                save_cookie();
                update_canvas();
                update_color();
                save_cookie();
            }
        });
    });

    // initialize slider
    slider_r.addEventListener("input", function () {
        normalized_field_r.value = slider_r.value;
        byte_field_r.value = Math.round(slider_r.value * 255);
        update_lch();
        update_hex();
        update_canvas();
        update_color();
        save_cookie();
    });
    slider_g.addEventListener("input", function () {
        normalized_field_g.value = slider_g.value;
        byte_field_g.value = Math.round(slider_g.value * 255);
        update_lch();
        update_hex();
        update_canvas();
        update_color();
        save_cookie();
    });
    slider_b.addEventListener("input", function () {
        normalized_field_b.value = slider_b.value;
        byte_field_b.value = Math.round(slider_b.value * 255);
        update_lch();
        update_hex();
        update_canvas();
        update_color();
        save_cookie();
    });
    slider_l.addEventListener("input", function () {
        normalized_field_l.value = slider_l.value;
        update_rgb();
        update_hex();
        update_canvas();
        update_color();
        save_cookie();
    });
    slider_c.addEventListener("input", function () {
        normalized_field_c.value = slider_c.value;
        update_rgb();
        update_hex();
        update_canvas();
        update_color()
        save_cookie();
    });
    slider_h.addEventListener("input", function () {
        normalized_field_h.value = slider_h.value;
        update_rgb();
        update_hex();
        update_canvas();
        update_color();
        save_cookie();
    });

    // initialize warnings
    warning_r.addEventListener('click', function (){
        fix_color("r");
    });
    warning_g.addEventListener('click', function (){
        fix_color("g");
    });
    warning_b.addEventListener('click', function (){
        fix_color("b");
    });
    function fix_color(c) {
        switch (c) {
            case "r":
                let r = Number(normalized_field_r.value);
                if (r < 0) {
                    r = 0;
                }
                if (r > 1) {
                    r = 1;
                }
                normalized_field_r.value = r.toFixed(3);
                byte_field_r.value = Math.round(r * 255);
                break;
            case "g":
                let g = Number(normalized_field_g.value);
                if (g < 0) {
                    g = 0;
                }
                if (g > 1) {
                    g = 1;
                }
                normalized_field_g.value = g.toFixed(3);
                byte_field_g.value = Math.round(g * 255);
                break;
            case "b":
                let b = Number(normalized_field_b.value);
                if (b < 0) {
                    b = 0;
                }
                if (b > 1) {
                    b = 1;
                }
                normalized_field_b.value = b.toFixed(3);
                byte_field_b.value = Math.round(b * 255);
                break;
        }

        slider_r.value = normalized_field_r.value;
        slider_g.value = normalized_field_g.value;
        slider_b.value = normalized_field_b.value;
        slider_l.value = normalized_field_l.value;
        slider_c.value = normalized_field_c.value;
        slider_h.value = normalized_field_h.value;

        update_lch();
        update_color();
        update_canvas();
        update_hex();
        save_cookie();
    }

    // initialize background overlay
    background_overlay.addEventListener("mousedown", (e) => {
        hide_ui(false);
    });

    canvas_interpolation.addEventListener("mousedown", (e) => {
        hide_ui(false);
    });

    document.addEventListener('keydown', (e) => {
        if (event.key === "Escape") {
            hide_ui(false);
        }
    });

    function hide_ui(value) {
        const overlay_display = value ? "block" : "none";
        const overlay_color = background_actual.style.background;

        background_overlay.style.display = overlay_display;
        background_overlay.style.background = overlay_color;
    }

    // initialize other
    background_label.addEventListener('click', function (){
        select(-1);
    });

    // initialize from cookie
    initialize_everything();
    function initialize_everything() {
        read_cookie();

        select(cookie.selected);

        const lab = get_color();
        const lch = oklab_to_oklch(lab);
        const normalized_rgb = oklab_to_linear_srgb(lab);
        const byte_rgb = {
            r: Math.round(normalized_rgb.r * 255),
            g: Math.round(normalized_rgb.g * 255),
            b: Math.round(normalized_rgb.b * 255),
        };

        slider_r.value = normalized_rgb.r;
        slider_g.value = normalized_rgb.g;
        slider_b.value = normalized_rgb.b;
        slider_l.value = lch.l;
        slider_c.value = lch.c;
        slider_h.value = lch.h;
        byte_field_r.value = byte_rgb.r;
        byte_field_g.value = byte_rgb.g;
        byte_field_b.value = byte_rgb.b;
        normalized_field_r.value = normalized_rgb.r.toFixed(3);
        normalized_field_g.value = normalized_rgb.g.toFixed(3);
        normalized_field_b.value = normalized_rgb.b.toFixed(3);
        normalized_field_l.value = lch.l.toFixed(3);
        normalized_field_c.value = lch.c.toFixed(3);
        normalized_field_h.value = lch.h.toFixed(3);

        background_hex_field.value = lab_to_hex(cookie.background).code;

        update_canvas();
        update_hex();
        update_background();

        clamp_dropdown.value = cookie.clamp_invalid_colors ? "clamp" : "hide";
        format_dropdown.value = cookie.format_field;
        interpolation_dropdown.value = cookie.interpolation;

        clamp_dropdown_change_callback();
        format_dropdown_change_callback();

        document.querySelector(`input[name="z_coord"][value="${cookie.selected_z}"]`).checked = true;

        redraw_colors();
        redraw_interpolation();
    }

    // callbacks
    function select(index) {
        cookie.selected = index;

        const lab = get_color();
        const lch = oklab_to_oklch(lab);
        normalized_field_l.value = lch.l;
        normalized_field_c.value = lch.c;
        normalized_field_h.value = lch.h;

        if (index < 0) {
            background_label.style.border = "2px solid black";
            background_label.style.outline = "2px solid white";
        } else {
            background_label.style.border = "2px solid rgba(0, 0, 0, 0)";
            background_label.style.outline = "2px solid rgba(0, 0, 0, 0)";
        }

        update_rgb();
        update_lch();
        update_hex();
        update_canvas();
        update_color();
        redraw_colors();
        redraw_interpolation();
        save_cookie();
    }

    function update_rgb() {
        const lch = {
            l: Number(normalized_field_l.value),
            h: Number(normalized_field_h.value),
            c: Number(normalized_field_c.value),
        };
        const rgb = oklch_to_linear_srgb(lch);

        normalized_field_r.value = rgb.r.toFixed(3);
        normalized_field_g.value = rgb.g.toFixed(3);
        normalized_field_b.value = rgb.b.toFixed(3);
        byte_field_r.value = Math.round(rgb.r * 255);
        byte_field_g.value = Math.round(rgb.g * 255);
        byte_field_b.value = Math.round(rgb.b * 255);
        slider_r.value = normalized_field_r.value;
        slider_g.value = normalized_field_g.value;
        slider_b.value = normalized_field_b.value;
    }

    function update_lch() {
        const rgb = {
            r: Number(normalized_field_r.value),
            g: Number(normalized_field_g.value),
            b: Number(normalized_field_b.value),
        };
        const lch = linear_srgb_to_oklch(rgb);

        normalized_field_l.value = lch.l.toFixed(3);
        normalized_field_c.value = lch.c.toFixed(3);
        normalized_field_h.value = lch.h.toFixed(3);
        slider_l.value = normalized_field_l.value;
        slider_c.value = normalized_field_c.value;
        slider_h.value = normalized_field_h.value;
    }

    function update_hex() {
        let r = Number(byte_field_r.value);
        let g = Number(byte_field_g.value);
        let b = Number(byte_field_b.value);

        let r_is_valid = true;
        let g_is_valid = true;
        let b_is_valid = true;
        if (r < 0) {
            r = 0;
            r_is_valid = false;
        }
        if (r > 255) {
            r = 255;
            r_is_valid = false;
        }
        if (g < 0) {
            g = 0;
            g_is_valid = false;
        }
        if (g > 255) {
            g = 255;
            g_is_valid = false;
        }
        if (b < 0) {
            b = 0;
            b_is_valid = false;
        }
        if (b > 255) {
            b = 255;
            b_is_valid = false;
        }

        warning_r.style.visibility = r_is_valid ? "hidden" : "visible";
        warning_g.style.visibility = g_is_valid ? "hidden" : "visible";
        warning_b.style.visibility = b_is_valid ? "hidden" : "visible";

        let hex_r = r.toString(16);
        if (hex_r.length == 1) {
            hex_r = `0${hex_r}`;
        }
        let hex_g = g.toString(16);
        if (hex_g.length == 1) {
            hex_g = `0${hex_g}`;
        }
        let hex_b = b.toString(16);
        if (hex_b.length == 1) {
            hex_b = `0${hex_b}`;
        }
        const hex_string = `${hex_r}${hex_g}${hex_b}`;
        hex_field.value = hex_string;
    }

    function update_color() {
        const lch = {
            l: Number(normalized_field_l.value),
            c: Number(normalized_field_c.value),
            h: Number(normalized_field_h.value),
        };
        const lab = oklch_to_oklab(lch);

        if (cookie.selected < 0) {
            cookie.background = lab;
            const hex = lab_to_hex(lab);
            background_hex_field.value = hex.code;
            update_background();
        } else {
            // set selected color
            cookie.colors[cookie.selected] = lab;
            redraw_colors();
            redraw_interpolation();
        }
    }

    function get_color() {
        if (cookie.selected < 0) {
            return cookie.background;
        } else {
            return cookie.colors[cookie.selected];
        }
    }

    function update_background() {
        const lab = cookie.background;
        const hex = lab_to_hex(lab);
        const foreground = get_foreground_color(hex);
        background_actual.style.background = `#${hex.code}`;
        labels.forEach(label => {
            label.style.color = foreground.color_string;
        });
    }

    function clamp_dropdown_change_callback() {
        const selected_value = clamp_dropdown.value;
        cookie.clamp_invalid_colors = selected_value == "clamp";

        update_canvas();
        update_color();
        save_cookie();
    }

    function format_dropdown_change_callback() {
        const selected_value = format_dropdown.value;
        const show_byte_field = selected_value === "byte";
        const byte_display = show_byte_field ? "block" : "none";
        const normalized_display = show_byte_field ? "none" : "block";
        byte_field_r.style.display = byte_display;
        byte_field_g.style.display = byte_display;
        byte_field_b.style.display = byte_display;
        normalized_field_r.style.display = normalized_display;
        normalized_field_g.style.display = normalized_display;
        normalized_field_b.style.display = normalized_display;

        cookie.format_field = format_dropdown.value;
        save_cookie();
    }

    function interpolation_dropdown_change_callback() {
        const selected_value = interpolation_dropdown.value;
        cookie.interpolation = selected_value;
        redraw_interpolation();
        save_cookie();
    }

    function handle_rgb_input(input, is_byte_field) {
        const number = is_byte_field
            ? parseInt(input.value)
            : parseFloat(input.value);

        if (isNaN(number)) {
            input.value = 0;
        } else if (number < input.min) {
            input.value = input.min;
        } else if (number > input.max) {
            input.value = input.max;
        }

        if (is_byte_field) {
            const byte_r = byte_field_r.value;
            const byte_g = byte_field_g.value;
            const byte_b = byte_field_b.value;
            const normalized_r = (byte_r / 255).toFixed(3);
            const normalized_g = (byte_g / 255).toFixed(3);
            const normalized_b = (byte_b / 255).toFixed(3);
            normalized_field_r.value = normalized_r;
            normalized_field_g.value = normalized_g;
            normalized_field_b.value = normalized_b;
        } else { // is_normalized_field
            const normalized_r = normalized_field_r.value;
            const normalized_g = normalized_field_g.value;
            const normalized_b = normalized_field_b.value;
            const byte_r = Math.round(normalized_r * 255);
            const byte_g = Math.round(normalized_g * 255);
            const byte_b = Math.round(normalized_b * 255);
            byte_field_r.value = byte_r;
            byte_field_g.value = byte_g;
            byte_field_b.value = byte_b;
        }

        slider_r.value = normalized_field_r.value;
        slider_g.value = normalized_field_g.value;
        slider_b.value = normalized_field_b.value;

        update_lch();
        update_hex();
        update_canvas();
        update_color();
        save_cookie();
    }

    function handle_lch_input(input) {
        const number = parseFloat(input.value);

        if (isNaN(number)) {
            input.value = 0;
        } else if (number < input.min) {
            input.value = input.min;
        } else if (number > input.max) {
            input.value = input.max;
        }

        slider_l.value = normalized_field_l.value;
        slider_c.value = normalized_field_c.value;
        slider_h.value = normalized_field_h.value;

        update_rgb();
        update_hex();
        update_canvas();
        update_color();
        save_cookie();
    }

    function validate_hex(input) {
        let value = input.value.trim().substr(0, 6);
        for (let i = 0; i < value.length;) {
            let character = value[i];

            var is_hex = /^[0-9a-fA-F]+$/.test(character);
            if (!is_hex) {
                value = `${value.slice(0, i)}${value.slice(i + 1)}`;
            } else {
                i += 1;
            }
        }

        input.value = value;
        while (value.length < 6) {
            value = `${value}0`;
        }

        const hex_r = value.substr(0,2);
        const hex_g = value.substr(2,2);
        const hex_b = value.substr(4,2);
        const byte_r = parseInt(hex_r, 16);
        const byte_g = parseInt(hex_g, 16);
        const byte_b = parseInt(hex_b, 16);

        return {
            r: byte_r,
            g: byte_g,
            b: byte_b,
            code: value,
        };
    }

    function handle_hex_input(input) {
        const hex = validate_hex(input);

        const normalized_r = hex.r / 255;
        const normalized_g = hex.g / 255;
        const normalized_b = hex.b / 255;
        const rgb = {
            r: normalized_r,
            g: normalized_g,
            b: normalized_b,
        }
        const lch = linear_srgb_to_oklch(rgb);

        byte_field_r.value = hex.r;
        byte_field_g.value = hex.g;
        byte_field_b.value = hex.b;
        normalized_field_r.value = rgb.r.toFixed(3);
        normalized_field_g.value = rgb.g.toFixed(3);
        normalized_field_b.value = rgb.b.toFixed(3);
        normalized_field_l.value = lch.l.toFixed(3);
        normalized_field_c.value = lch.c.toFixed(3);
        normalized_field_h.value = lch.h.toFixed(3);

        slider_r.value = normalized_field_r.value;
        slider_g.value = normalized_field_g.value;
        slider_b.value = normalized_field_b.value;
        slider_l.value = normalized_field_l.value;
        slider_c.value = normalized_field_c.value;
        slider_h.value = normalized_field_h.value;

        warning_r.style.visibility = "hidden";
        warning_g.style.visibility = "hidden";
        warning_b.style.visibility = "hidden";

        update_canvas();
        update_color();
        save_cookie();
    }

    function handle_background_hex_input(input) {
        const hex = validate_hex(input);
        const lab = hex_to_lab(hex);
        
        cookie.background = lab;
        update_background();

        cookie.selected = -1;
        background_label.style.border = "2px solid black";
        background_label.style.outline = "2px solid white";
        redraw_colors();

        save_cookie();
    }

    function redraw_colors() {
        let newHTML = "";

        for (let i = 0; i < cookie.colors.length; ++i) {
            const color = cookie.colors[i];
            const hex = lab_to_hex(color);
            const foreground = get_foreground_color(hex);
            const is_selected = i == cookie.selected;
            const border_style = is_selected
                ? "border: 2px solid black; outline: 2px solid white"
                : "border: 2px solid rgba(0, 0, 0, 0); outline: 2px solid rgba(0, 0, 0, 0)";
            newHTML += `
                <div id="color_div_${i}" style="display: inline-block; ${border_style};">
                    <table id="color_table_${i}" style="background:#${hex.code}">
                    <tr class="selectable" onclick="select(${i})"><td><span id="color_label_${i}" class="label" style="color: ${foreground.color_string}">Color ${i + 1}</span></td></tr>
                        <tr><td><input type="text" class="text_field" value="${hex.code}" oninput="handle_color_hex_input(this, ${i})"></td></tr>
                        <tr><td>
                            <div>
                                <button class="no-style" style="display: inline; width:32px;" onclick="move_left(${i})">&lt;</button>
                                <button class="no-style" style="display: inline; width:32px;" onclick="move_right(${i})">&gt;</button>
                            </div>
                        </td></tr>
                    </table>
                </div>`;
        }

        newHTML += `
                <div style="display: inline-block;">
                    <button class="no-style" onclick="add_color()">Add</button><br>
                    <button class="no-style" onclick="remove_color()">Remove</button><br><br>
                </div>`;

        color_container.innerHTML = newHTML;
    }

    function handle_color_hex_input(input, index) {
        const hex = validate_hex(input);
        const lab = hex_to_lab(hex);

        const lch = oklab_to_oklch(lab);
        normalized_field_l.value = lch.l;
        normalized_field_c.value = lch.c;
        normalized_field_h.value = lch.h;

        cookie.selected = index;
        background_label.style.border = "2px solid rgba(0, 0, 0, 0)";
        background_label.style.outline = "2px solid rgba(0, 0, 0, 0)";

        update_rgb();
        update_lch();
        update_hex();
        update_canvas();

        //update_color();
        cookie.colors[cookie.selected] = lab;

        //redraw_colors();
        for(let i = 0; i < cookie.colors.length; ++i) {
            const color = cookie.colors[i];
            const foreground = get_foreground_color(lab_to_hex(color));
            const is_selected = i == cookie.selected;
            const border_style = is_selected
                ? "border: 2px solid black; outline: 2px solid white"
                : "border: 2px solid rgba(0, 0, 0, 0); outline: 2px solid rgba(0, 0, 0, 0)";

                const color_div = document.getElementById(`color_div_${i}`);
                color_div.style = `display: inline-block; ${border_style};`;

                if (is_selected) {
                    const color_table = document.getElementById(`color_table_${i}`);
                    const color_label = document.getElementById(`color_label_${i}`);
                    color_table.style = `background:#${hex.code}`;
                    color_label.style = `color: ${foreground.color_string}`;
                }
        }


        redraw_interpolation();
        save_cookie();
    }

    function add_color() {
        const rgb = {
            r: Math.random(),
            g: Math.random(),
            b: Math.random(),
        };
        const lab = linear_srgb_to_oklab(rgb);
        
        if (cookie.selected < 0) {
            cookie.colors.push(lab);
            select(cookie.colors.length - 1);
        } else {
            cookie.colors.splice(cookie.selected + 1, 0, lab);
            select(cookie.selected + 1);
        }
    }

    function remove_color() {
        if (cookie.colors.length == 1) {
            return;
        }
        
        if (cookie.selected < 0) {
            return;
        }

        cookie.colors.splice(cookie.selected, 1);
        
        const new_selected = cookie.selected >= cookie.colors.length
            ? cookie.colors.length - 1
            : cookie.selected;
        select(new_selected);
    }

    function move_left(index) {
        if (index == 0) {
            return;
        }

        const l = index - 1;
        const r = index;

        [cookie.colors[r], cookie.colors[l]] = [cookie.colors[l], cookie.colors[r]];
        select(l);
    }

    function move_right(index) {
        if (index + 1 == cookie.colors.length) {
            return;
        }

        const l = index;
        const r = index + 1;

        [cookie.colors[r], cookie.colors[l]] = [cookie.colors[l], cookie.colors[r]];
        select(r);
    }

    function redraw_interpolation() {
        const width = canvas_interpolation.width;
        const height = canvas_interpolation.height;
        const imageData = ctx_interpolation.createImageData(width, height);
        const data = imageData.data;

        switch(cookie.interpolation) {
            case "none": {
                const splits = cookie.colors.length;

                for (let y = 0; y <= height; ++y) {
                    for (let x = 0; x < width; ++x) {
                        const inverted_y = height - y;
                        const i = (inverted_y * width + x) * 4; // each pixel has 4 values: r, g, b, a

                        const normalized_x = x / width;
                        const scaled = normalized_x * splits;
                        const lower = Math.floor(scaled);
                        const upper = Math.ceil(scaled);
                        const lerp = scaled % 1;

                        const lab = cookie.colors[lower];
                        const rgb = oklab_to_linear_srgb(lab);

                        data[i + 0] = rgb.r * 255;
                        data[i + 1] = rgb.g * 255;
                        data[i + 2] = rgb.b * 255;
                        data[i + 3] = 255;
                    }
                }

                break;
            }
            case "oklab": {
                const splits = cookie.colors.length - 1;

                for (let y = 0; y <= height; ++y) {
                    for (let x = 0; x < width; ++x) {
                        const inverted_y = height - y;
                        const i = (inverted_y * width + x) * 4; // each pixel has 4 values: r, g, b, a

                        const normalized_x = x / width;
                        const scaled = normalized_x * splits;
                        const lower = Math.floor(scaled);
                        const upper = Math.ceil(scaled);
                        const lerp = scaled % 1;

                        const lab_1 = cookie.colors[lower];
                        const lab_2 = cookie.colors[upper];

                        const l = lab_1.l + lerp * (lab_2.l - lab_1.l);
                        const a = lab_1.a + lerp * (lab_2.a - lab_1.a);
                        const b = lab_1.b + lerp * (lab_2.b - lab_1.b);
                        const lab = {
                            l: l,
                            a: a,
                            b: b,
                        };
                        const rgb = oklab_to_linear_srgb(lab);

                        data[i + 0] = rgb.r * 255;
                        data[i + 1] = rgb.g * 255;
                        data[i + 2] = rgb.b * 255;
                        data[i + 3] = 255;
                    }
                }

                break;
            }
            case "rgb": {
                const splits = cookie.colors.length - 1;

                for (let y = 0; y <= height; ++y) {
                    for (let x = 0; x < width; ++x) {
                        const inverted_y = height - y;
                        const i = (inverted_y * width + x) * 4; // each pixel has 4 values: r, g, b, a

                        const normalized_x = x / width;
                        const scaled = normalized_x * splits;
                        const lower = Math.floor(scaled);
                        const upper = Math.ceil(scaled);
                        const lerp = scaled % 1;

                        const lab_1 = cookie.colors[lower];
                        const lab_2 = cookie.colors[upper];
                        const rgb_1 = oklab_to_linear_srgb(lab_1);
                        const rgb_2 = oklab_to_linear_srgb(lab_2);

                        const r = rgb_1.r + lerp * (rgb_2.r - rgb_1.r);
                        const g = rgb_1.g + lerp * (rgb_2.g - rgb_1.g);
                        const b = rgb_1.b + lerp * (rgb_2.b - rgb_1.b);

                        data[i + 0] = r * 255;
                        data[i + 1] = g * 255;
                        data[i + 2] = b * 255;
                        data[i + 3] = 255;
                    }
                }

                break;
            }
        }

        ctx_interpolation.putImageData(imageData, 0, 0);
    }

    function reset_everything() {
        const selected_z = cookie.selected_z;
        const clamp_invalid_colors = cookie.clamp_invalid_colors;
        const format_field = cookie.format_field;
        const interpolation = cookie.interpolation;

        clear_cookie();
        set_cookie_to_default();

        cookie.selected_z = selected_z;
        cookie.clamp_invalid_colors = clamp_invalid_colors;
        cookie.format_field = format_field;
        cookie.interpolation = interpolation;

        save_cookie();

        initialize_everything();
    }

    // canvas functions
    function update_canvas() {
        const byte_rgb = {
            r: Number(byte_field_r.value),
            g: Number(byte_field_g.value),
            b: Number(byte_field_b.value),
        };
        const normalized_rgb = {
            r: Number(normalized_field_r.value),
            g: Number(normalized_field_g.value),
            b: Number(normalized_field_b.value),
        };
        const lch = {
            l: Number(normalized_field_l.value),
            c: Number(normalized_field_c.value),
            h: Number(normalized_field_h.value),
        };

        // xy
        let width = canvas_xy.width;
        let height = canvas_xy.height;
        let imageData = ctx_xy.createImageData(width, height);
        let data = imageData.data;

        for (let y = 0; y <= height; ++y) {
            for (let x = 0; x < width; ++x) {
                const inverted_y = height - y;
                const i = (inverted_y * width + x) * 4; // each pixel has 4 values: r, g, b, a

                const normalized_x = x / width;
                const normalized_y = y / height;
                let selected_x;
                let selected_y;

                switch (cookie.selected_z) {
                    case "r":
                        selected_x = byte_rgb.g;
                        selected_y = byte_rgb.b;
                        data[i + 0] = byte_rgb.r;
                        data[i + 1] = normalized_x * 255;
                        data[i + 2] = normalized_y * 255;
                        break;
                    case "g":
                        selected_x = byte_rgb.r;
                        selected_y = byte_rgb.b;
                        data[i + 0] = normalized_x * 255;
                        data[i + 1] = byte_rgb.g;
                        data[i + 2] = normalized_y * 255;
                        break;
                    case "b":
                        selected_x = byte_rgb.r;
                        selected_y = byte_rgb.g;
                        data[i + 0] = normalized_x * 255;
                        data[i + 1] = normalized_y * 255;
                        data[i + 2] = byte_rgb.b;
                        break;
                    case "l": {
                        selected_x = Math.round((lch.c / 0.4) * 255);
                        selected_y = Math.round(((lch.h + Math.PI) / (2 * Math.PI)) * 255);
                        const lch_ = {
                            l: lch.l,
                            c: normalized_x * 0.4,
                            h: normalized_y * 2 * Math.PI - Math.PI,
                        };
                        const normalized_rgb_ = clamp_rgb(oklch_to_linear_srgb(lch_));

                        data[i + 0] = normalized_rgb_.r * 255;
                        data[i + 1] = normalized_rgb_.g * 255;
                        data[i + 2] = normalized_rgb_.b * 255;
                        break;
                    }
                    case "c": {
                        selected_x = Math.round((lch.l) * 255);
                        selected_y = Math.round(((lch.h + Math.PI) / (2 * Math.PI)) * 255);
                        const lch_ = {
                            l: normalized_x,
                            c: lch.c,
                            h: normalized_y * 2 * Math.PI - Math.PI,
                        };
                        const rgb_ = clamp_rgb(oklch_to_linear_srgb(lch_));

                        data[i + 0] = rgb_.r * 255;
                        data[i + 1] = rgb_.g * 255;
                        data[i + 2] = rgb_.b * 255;
                        break;
                    }
                    case "h": {
                        selected_x = Math.round((lch.l) * 255);
                        selected_y = Math.round((lch.c / 0.4) * 255);
                        const lch_ = {
                            l: normalized_x,
                            c: normalized_y * 0.4,
                            h: lch.h,
                        };
                        const rgb_ = clamp_rgb(oklch_to_linear_srgb(lch_));

                        data[i + 0] = rgb_.r * 255;
                        data[i + 1] = rgb_.g * 255;
                        data[i + 2] = rgb_.b * 255;
                        break;
                    }
                }

                data[i + 3] = 255;

                if (selected_x == x || selected_y == y) {
                    const data_rgb = {
                        r: data[i + 0],
                        g: data[i + 1],
                        b: data[i + 1],
                    };
                    const foreground = get_foreground_color(data_rgb);
                    data[i + 0] = foreground.byte_r;
                    data[i + 1] = foreground.byte_g;
                    data[i + 2] = foreground.byte_b;
                }
            }
        }

        ctx_xy.putImageData(imageData, 0, 0);

        // z
        width = canvas_z.width;
        height = canvas_z.height;
        imageData = ctx_z.createImageData(width, height);
        data = imageData.data;

        for (let y = 0; y <= height; ++y) {
            for (let x = 0; x < width; ++x) {
                const inverted_y = height - y;
                const i = (inverted_y * width + x) * 4; // each pixel has 4 values: r, g, b, a

                const normalized_y = y / height;
                let selected_z;

                switch (cookie.selected_z) {
                    case "r":
                        selected_z = byte_rgb.r;
                        data[i + 0] = normalized_y * 255;
                        data[i + 1] = byte_rgb.g;
                        data[i + 2] = byte_rgb.b;
                        break;
                    case "g":
                        selected_z = byte_rgb.g;
                        data[i + 0] = byte_rgb.r;
                        data[i + 1] = normalized_y * 255;
                        data[i + 2] = byte_rgb.b;
                        break;
                    case "b":
                        selected_z = byte_rgb.b;
                        data[i + 0] = byte_rgb.r;
                        data[i + 1] = byte_rgb.g;
                        data[i + 2] = normalized_y * 255;
                        break;
                    case "l": {
                        selected_z = Math.round((lch.l) * 255);
                        const lch_ = {
                            l: normalized_y,
                            c: lch.c,
                            h: lch.h,
                        };
                        const rgb_ = clamp_rgb(oklch_to_linear_srgb(lch_));

                        data[i + 0] = rgb_.r * 255;
                        data[i + 1] = rgb_.g * 255;
                        data[i + 2] = rgb_.b * 255;
                        break;
                    }
                    case "c": {
                        selected_z = Math.round((lch.c / 0.4) * 255);
                        const lch_ = {
                            l: lch.l,
                            c: normalized_y * 0.4,
                            h: lch.h,
                        };
                        const normalized_rgb_ = clamp_rgb(oklch_to_linear_srgb(lch_));

                        data[i + 0] = normalized_rgb_.r * 255;
                        data[i + 1] = normalized_rgb_.g * 255;
                        data[i + 2] = normalized_rgb_.b * 255;
                        break;
                    }
                    case "h": {
                        selected_z = Math.round(((lch.h + Math.PI) / (2 * Math.PI)) * 255);
                        const lch_ = {
                            l: lch.l,
                            c: lch.c,
                            h: normalized_y * 2 * Math.PI - Math.PI,
                        };
                        const normalized_rgb_ = clamp_rgb(oklch_to_linear_srgb(lch_));

                        data[i + 0] = normalized_rgb_.r * 255;
                        data[i + 1] = normalized_rgb_.g * 255;
                        data[i + 2] = normalized_rgb_.b * 255;
                        break;
                    }
                }

                data[i + 3] = 255;

                if (selected_z == y) {
                    const data_rgb = {
                        r: data[i + 0],
                        g: data[i + 1],
                        b: data[i + 2],
                    };
                    const foreground = get_foreground_color(data_rgb);
                    data[i + 0] = foreground.byte_r;
                    data[i + 1] = foreground.byte_g;
                    data[i + 2] = foreground.byte_b;
                }
            }
        }

        ctx_z.putImageData(imageData, 0, 0);

        // update labels
        switch (cookie.selected_z) {
            case "r": {
                canvas_label_x.innerHTML = "B";
                canvas_label_x.title = "Blue";
                canvas_label_y.innerHTML = "G";
                canvas_label_y.title = "Green";
                canvas_label_z.innerHTML = "R";
                canvas_label_z.title = "Red";
                break;
            }
            case "g": {
                canvas_label_x.innerHTML = "B";
                canvas_label_x.title = "Blue";
                canvas_label_y.innerHTML = "R";
                canvas_label_y.title = "Red";
                canvas_label_z.innerHTML = "G";
                canvas_label_z.title = "Green";
                break;
            }
            case "b": {
                canvas_label_x.innerHTML = "G";
                canvas_label_x.title = "Green";
                canvas_label_y.innerHTML = "R";
                canvas_label_y.title = "Red";
                canvas_label_z.innerHTML = "B";
                canvas_label_z.title = "Blue";
                break;
            }
            case "l": {
                canvas_label_x.innerHTML = "H";
                canvas_label_x.title = "OkLab Hue";
                canvas_label_y.innerHTML = "C";
                canvas_label_y.title = "OkLab Chroma";
                canvas_label_z.innerHTML = "L";
                canvas_label_z.title = "OkLab Luminocity";
                break;
            }
            case "c": {
                canvas_label_x.innerHTML = "H";
                canvas_label_x.title = "OkLab Hue";
                canvas_label_y.innerHTML = "L";
                canvas_label_y.title = "OkLab Luminocity";
                canvas_label_z.innerHTML = "C";
                canvas_label_z.title = "OkLab Chroma";
                break;
            }
            case "h": {
                canvas_label_x.innerHTML = "C";
                canvas_label_x.title = "OkLab Chroma";
                canvas_label_y.innerHTML = "L";
                canvas_label_y.title = "OkLab Luminocity";
                canvas_label_z.innerHTML = "H";
                canvas_label_z.title = "OkLab Hue";
                break;
            }
        }
    }

    // color functions
    function get_foreground_color(background_byte_rgb) {
        const rgb = {
            r: background_byte_rgb.r / 255,
            g: background_byte_rgb.g / 255,
            b: background_byte_rgb.b / 255,
        };
        const lab = linear_srgb_to_oklab(rgb);
        if (lab.l > 0.85) {
            return {
                byte_r: 0,
                byte_g: 0,
                byte_b: 0,
                color_string: "black",
            };
        } else {
            return {
                byte_r: 255,
                byte_g: 255,
                byte_b: 255,
                color_string: "white",
            };
        }
    }

    function clamp_rgb(c) {
        let clamped_r = c.r;
        let clamped_g = c.g;
        let clamped_b = c.b;
        let byte_r = Math.round(c.r * 255);
        let byte_g = Math.round(c.g * 255);
        let byte_b = Math.round(c.b * 255);
        let was_clamped = false;

        if (byte_r < 0) {
            clamped_r = 0;
            was_clamped = true;
        }
        if (byte_r > 255) {
            clamped_r = 1;
            was_clamped = true;
        }
        if (byte_g < 0) {
            clamped_g = 0;
            was_clamped = true;
        }
        if (byte_g > 255) {
            clamped_g = 1;
            was_clamped = true;
        }
        if (byte_b < 0) {
            clamped_b = 0;
            was_clamped = true;
        }
        if (byte_b > 255) {
            clamped_b = 1;
            was_clamped = true;
        }

        if (was_clamped && !cookie.clamp_invalid_colors) {
            return {
                r: 1,
                g: 0,
                b: 1,
            };
        } else {
            return {
                r: clamped_r,
                g: clamped_g,
                b: clamped_b,
            };
        }

    }

    function linear_srgb_to_oklch(c) {
        return oklab_to_oklch(linear_srgb_to_oklab(c));
    }

    function oklch_to_linear_srgb(c) {
        return oklab_to_linear_srgb(oklch_to_oklab(c));
    }

    function linear_srgb_to_oklab(c) {
        const l = 0.4122214708 * c.r + 0.5363325363 * c.g + 0.0514459929 * c.b;
        const m = 0.2119034982 * c.r + 0.6806995451 * c.g + 0.1073969566 * c.b;
        const s = 0.0883024619 * c.r + 0.2817188376 * c.g + 0.6299787005 * c.b;

        const l_ = Math.cbrt(l);
        const m_ = Math.cbrt(m);
        const s_ = Math.cbrt(s);

        return {
            l: 0.2104542553 * l_ + 0.7936177850 * m_ - 0.0040720468 * s_,
            a: 1.9779984951 * l_ - 2.4285922050 * m_ + 0.4505937099 * s_,
            b: 0.0259040371 * l_ + 0.7827717662 * m_ - 0.8086757660 * s_,
        };
    }

    function oklab_to_linear_srgb(c) {
        const l_ = c.l + 0.3963377774 * c.a + 0.2158037573 * c.b;
        const m_ = c.l - 0.1055613458 * c.a - 0.0638541728 * c.b;
        const s_ = c.l - 0.0894841775 * c.a - 1.2914855480 * c.b;

        const l = l_ * l_ * l_;
        const m = m_ * m_ * m_;
        const s = s_ * s_ * s_;

        return {
            r: +4.0767416621 * l - 3.3077115913 * m + 0.2309699292 * s,
            g: -1.2684380046 * l + 2.6097574011 * m - 0.3413193965 * s,
            b: -0.0041960863 * l - 0.7034186147 * m + 1.7076147010 * s,
        };
    }

    function oklab_to_oklch(c) {
        return {
            l: c.l,
            c: Math.sqrt(c.a * c.a + c.b * c.b),
            h: Math.atan2(c.b, c.a),
        };
    }

    function oklch_to_oklab(c) {
        return {
            l: c.l,
            a: c.c * Math.cos(c.h),
            b: c.c * Math.sin(c.h),
        }
    }

    function hex_to_lab(hex) {
        const normalized_r = hex.r / 255;
        const normalized_g = hex.g / 255;
        const normalized_b = hex.b / 255;
        const rgb = {
            r: normalized_r,
            g: normalized_g,
            b: normalized_b,
        }
        const lab = linear_srgb_to_oklab(rgb);
        return lab;
    }

    function lab_to_hex(lab) {
        const rgb = oklab_to_linear_srgb(lab);
        let byte_r = Math.round(rgb.r * 255);
        let byte_g = Math.round(rgb.g * 255);
        let byte_b = Math.round(rgb.b * 255);

        if (byte_r < 0) {
            byte_r = 0;
        }
        if (byte_r > 255) {
            byte_r = 255;
        }
        if (byte_g < 0) {
            byte_g = 0;
        }
        if (byte_g > 255) {
            byte_g = 255;
        }
        if (byte_b < 0) {
            byte_b = 0;
        }
        if (byte_b > 255) {
            byte_b = 255;
        }

        let hex_r = byte_r.toString(16);
        if (hex_r.length == 1) {
            hex_r = `0${hex_r}`;
        }
        let hex_g = byte_g.toString(16);
        if (hex_g.length == 1) {
            hex_g = `0${hex_g}`;
        }
        let hex_b = byte_b.toString(16);
        if (hex_b.length == 1) {
            hex_b = `0${hex_b}`;
        }
        const hex_string = `${hex_r}${hex_g}${hex_b}`;

        return {
            r: byte_r,
            g: byte_g,
            b: byte_b,
            code: hex_string,
        };
    }

    // cookie functions
    function set_cookie_to_default() {
        cookie = {
            selected_z: "h", // r, g, b, l, c, h,
            clamp_invalid_colors: false,
            color_l: 0.828,
            color_a: -0.018675723091779266,
            color_b: -0.0818976029380542,
            format_field: "normalized", // "normalized", "byte",
            interpolation: "oklab", // "none", "oklab", "rgb"
            background: {
                l: 0.9850323339423428,
                a: 0.004844496104840446,
                b: 0.007201155049766039,
            },
            selected: 3, // negative numbers represent the background being selected
            colors: [
                {
                    "l": 0.775,
                    "a": 0.15189972128564153,
                    "b": -0.018315967715204662
                },
                {
                    "l": 0.93,
                    "a": -0.046818838978830137,
                    "b": 0.19032602637756713
                },
                {
                    "l": 0.992,
                    "a": 0,
                    "b": 0
                },
                {
                    "l": 0.881,
                    "a": -0.04897244697005773,
                    "b": -0.055477017203206615
                }
            ]
        }
    }

    function read_cookie() {
        let cookie_exists = false;
        const cookies = document.cookie.split("; ");
        for (let c of cookies) {
            const [key, value] = c.split("=");
            if (key === cookie_name) {
                try {
                    cookie = JSON.parse(value);
                    cookie_exists = true;
                } catch (e) {
                }

                break;
            }
        }

        if (!cookie_exists) {
            set_cookie_to_default();
        }
    }

    function save_cookie() {
        // get expiration date 1 week from now
        const date = new Date();
        date.setDate(date.getDate() + 7);
        const expiration_date = date.toUTCString();

        const cookie_data = JSON.stringify(cookie);

        document.cookie = `${cookie_name}=${cookie_data}; path=/; expires=${expiration_date}`
        //console.log(document.cookie);
    }

    function clear_cookie() {
        document.cookie = `${cookie_name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
    }
</script>