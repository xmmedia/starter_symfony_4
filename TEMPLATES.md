# Code Templates

### Forms

The new view:
```
{% extends '::admin.html.twig' %}

{% block page_title %}Add Category | {% endblock %}
{% block header_page_title %}Add Category{% endblock %}

{% block content %}
    {% include 'flash_messages.html.twig' %}

    {% embed 'AdminCategory/form.html.twig' %}{% endembed %}
{% endblock content %}
```

The edit view:
```
{% extends '::admin.html.twig' %}

{% block page_title %}Add Category | {% endblock %}
{% block header_page_title %}Add Category{% endblock %}

{% block content %}
    {% include 'flash_messages.html.twig' %}

    {% embed 'AdminCategory/form.html.twig' %}
        {% block extra_actions %}
            <ul class="form-extra_actions">
                <li>
                    {{
                        form_start(delete_form, {
                            'attr' : {
                                'class' : 'js-form-delete',
                                'data-record-desc' : 'Category'
                            }
                        })
                    }}
                        <button class="form-action button-as_link">Delete</button>
                    {{ form_end(delete_form) }}
                </li>
            </ul>
        {% endblock extra_actions %}
    {% endembed %}
{% endblock content %}
```

The form view:
```
<div class="form-wrap">
    {{ form_start(form) }}
        {{ form_errors(form) }}

        {{ form_row(form.name) }}

        <div class="form-button_wrap">
            <button>Save</button>
            <a href="{{ path('admin_category_list') }}" class="form-action">Back to list</a>
        </div>
    {{ form_end(form) }}

    {% block extra_actions %}{% endblock %}
</div>
```