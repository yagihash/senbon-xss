"use strict"
$(function () {
    $("p.markdown").each(function () {
        $(this).html(marked($(this).text()))
    })

    $(".stage-edit input[name=flag]").on({
        "focus": function (e) {
            $(this).attr("type", "text")
        },
        "focusout": function (e) {
            $(this).attr("type", "password")
        }
    })

    $("form.edit").on({
        "submit": function (e) {
            e.preventDefault()
            let self = this
            let url = this.action
            let data = $(this).serialize()
            $.ajax({
                type: "POST",
                url: url,
                timeout: 3000,
                cache: false,
                data: data,
            }).done(function (json) {
                $(self).find("input[type=submit]").val(json.data)
                $(self).find("input[type=submit]").addClass("success")
                updateTokens(json.csrf)
            }).fail(function (xhr) {
                switch (xhr.status) {
                    case 400:
                        $(self).find("input[type=submit]").val(xhr.responseText)
                        break
                    default:
                        var json = JSON.parse(xhr.responseText)
                        $(self).find("input[type=submit]").val(json.error)
                        updateTokens(json.csrf)
                        break
                }
                $(self).find("input[type=submit]").addClass("error")
            }).always(function () {
                setTimeout(function () {
                    $(self).find("input[type=submit]").attr("class", "stack")
                    $(self).find("input[type=submit]").val("Edit")
                }, 2000)
            })
        }
    })

    $("form.delete").on({
        "submit": function (e) {
            if (confirm("Are you sure to delete the stage?")) {

            } else {
                e.preventDefault()
            }
        }
    })

    $("a").each(function (index, elm) {
        let url = new URL(elm.href)
        if (url.origin !== document.origin)
            $(elm).attr("target", "_blank")
    })

    $("input[name=url]").on({
        "keyup": function (e) {
            let url = $(this).val()
            let isValid = !!(url.match(/^https?:\/\/.+$/))
            $(this).closest("form").children("input[type=submit]")[0].disabled = !isValid

        }
    })

    $("input[name=flag]").on({
        "keyup": function (e) {
            let flag = $(this).val()
            console.log(flag)
            let isValid = !!(flag.match(/^FLAG{.+}$/))
            $(this).closest("form").children("input[type=submit]")[0].disabled = !isValid
        }
    })

    function updateTokens(csrf) {
        $(`input[name=${csrf["keys"]["name"]}]`).val(csrf["name"])
        $(`input[name=${csrf["keys"]["value"]}]`).val(csrf["value"])
    }
})
