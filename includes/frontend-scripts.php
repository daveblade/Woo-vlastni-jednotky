<?php
add_action('wp_footer', function() {
    ?>
    <script>
        jQuery(function($) {
            function fixCartQuantityFields() {
                $(".cart_item").each(function() {
                    var cartRow = $(this);
                    var inputField = cartRow.find("input.qty");
                    var correctQty = cartRow.find(".product-quantity").text().match(/\d+/);

                    if (correctQty) {
                        var actualQty = parseInt(correctQty[0], 10);
                        if (inputField.val() != actualQty) {
                            inputField.val(actualQty).trigger("change");
                            console.log("✅ Opraveno množství v košíku:", actualQty);
                        }
                    }

                    // ✅ Oprava pro variantní produkty
                    var variationId = cartRow.data("variation_id") || cartRow.find("input[name^='variation_id']").val();
                    if (variationId && variationId !== "0") {
                        console.log("🛠️ Variantní produkt ID:", variationId);
                        inputField.attr("data-variation-id", variationId);
                    }
                });
            }

            // ✅ Oprava při načtení stránky
            $(document).ready(function() {
                fixCartQuantityFields();
            });

            // ✅ Oprava po AJAX aktualizaci košíku
            $(document.body).on("updated_wc_div", function() {
                fixCartQuantityFields();
            });

        });
    </script>
    <?php
});
?>
