# dz1_judge — проверка требований и тестов MILEAGE

## Область проверки

- Intent: `docs/intent/intent_MILEAGE.md`.
- Спецификация: `docs/spec/spec_MILEAGE.md`.
- Тесты: `tests/Unit/AssessmentServiceTest.php`,
  `tests/Unit/ApplicationValidatorTest.php`,
  `tests/Unit/DecisionEngineTest.php`.

## Матрица покрытия

| REQ | Требуемое поведение | Тесты | Статус |
|---|---|---|---|
| REQ-MILEAGE-01 | При LTV <= 62 пробег 399999 и 400000 даёт approve; 400001 понижает approve до review | `AssessmentServiceTest::testAppliesMileageRuleOnTheBoundary`, наборы `399999`, `400000`, `400001` | Покрыт |
| REQ-MILEAGE-02 | При высоком пробеге исходные review и reject сохраняются | `AssessmentServiceTest::testKeepsNonApproveDecisionWithHighMileage`, наборы `review по LTV сохраняется`, `reject по LTV сохраняется` | Покрыт |
| REQ-MILEAGE-03 | 400001--500000 валиден; 500000 допустим, 500001 невалиден | `AssessmentServiceTest::testAppliesMileageRuleOnTheBoundary`, набор `400001`; `ApplicationValidatorTest::testAcceptsMaximumMileage`, `testRejectsMileageAboveMaximum` | Покрыт |
| REQ-MILEAGE-04 | Отсутствующий, null и пустой `mileage` дают ошибку валидации | `ApplicationValidatorTest::testRejectsMissingMileageField`, `testRejectsNullMileage`, `testRejectsEmptyStringMileage` | Покрыт |
| REQ-MILEAGE-05 | У пониженного до review решения лимит равен 0 | `AssessmentServiceTest::testAppliesMileageRuleOnTheBoundary`, набор `400001` | Покрыт |
| REQ-MILEAGE-06 | Пороги LTV и существующие проверки не меняются | `DecisionEngineTest::testDecidesByLtv`, наборы `62.0`, `85.0`, `85.01`; `ApplicationValidatorTest::testKeepsExistingValidationRules`, наборы VIN, года, стоимости, суммы и срока | Покрыт |

## Результат текущего запуска

`make lint` завершился успешно. `make test` завершился успешно: 42 теста,
56 проверок. В том числе подтверждены отклонение пустой строки `mileage` и
понижение approve до review с нулевым лимитом при 400001 км.

## Проверка лишних требований

Лишних требований в `spec_MILEAGE.md` не обнаружено:

- REQ-MILEAGE-01 следует из intent, constraints п. 1.
- REQ-MILEAGE-02 следует из intent, замысла и constraints п. 2.
- REQ-MILEAGE-03 следует из intent, constraints пп. 4--5.
- REQ-MILEAGE-04 следует из intent, замысла и constraints п. 3.
- REQ-MILEAGE-05 следует из intent, constraints п. 6.
- REQ-MILEAGE-06 фиксирует неизменность правил из intent, constraints п. 4.

Поля причины review, фронтенд, снижение максимального пробега и LOAN-12
остались в разделе «Не входит», как предписывает intent.

## Итог

Каждый REQ-MILEAGE-01--06 сопоставлен хотя бы с одним тестом. Лишних
требований в спецификации нет. Все тесты проходят.
