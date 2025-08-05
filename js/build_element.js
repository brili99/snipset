function be(tag, attr = {}, children = [], events = {}) {
    // Create the element
    const element = document.createElement(tag);

    // Set attributes
    Object.keys(attr).forEach(key => {
        let value = attr[key];

        if (key === "class" && Array.isArray(value)) {
            value = value.join(" "); // Convert class array to string
        }

        element.setAttribute(key, value);
    });

    // Append children
    children.forEach(child => {
        if (typeof child === "string" || typeof child === "number") {
            if (typeof child === "string" && child.trim().startsWith("<")) {
                const parsedElement = parseHTMLString(child);
                if (parsedElement) element.appendChild(parsedElement);
            } else {
                element.appendChild(document.createTextNode(child));
            }
        } else if (child instanceof HTMLElement) {
            element.appendChild(child);
        }
    });

    // Add event listeners
    Object.keys(events).forEach(event => {
        element.addEventListener(event, events[event]);
    });

    return element;
}

function parseHTMLString(htmlString) {
    const template = document.createElement('template'); // Ensures proper parsing of elements like SVG
    template.innerHTML = htmlString.trim();
    return template.content.firstChild;
}
